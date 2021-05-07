<?php

namespace WFHtmlSanitizer;

use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\NodeInterface;
use HtmlSanitizer\Visitor\AbstractNodeVisitor;
use HtmlSanitizer\Visitor\NamedNodeVisitorInterface;
use HtmlSanitizer\Visitor\IsChildlessTagVisitorTrait;
use HtmlSanitizer\Extension\Image\Node\ImgNode;

class ImgNodeVisitor extends AbstractNodeVisitor implements NamedNodeVisitorInterface {
	use IsChildlessTagVisitorTrait;

	protected function getDomNodeName(): string {
		return 'img';
	}

	protected function createNode(\DOMNode $domNode, Cursor $cursor): NodeInterface {
		$node = new ImgNode($cursor->node);

		// Do we have image shortcoding enabled?
		if (array_key_exists('enabled', $this->config['waterfall-shortcodes']) && in_array('image', $this->config['waterfall-shortcodes']['enabled'] ?? [])) {
			// Does this image have a `data-image-id` attribute?
			if (!empty($imageID = $this->getAttribute($domNode, 'data-image-id'))) {
				// Yes it does, let's shortcode it
				$newNode = new ShortcodeNode($cursor->node);
				$newNode->setShortcode('{{IMAGE:{{' . $imageID . '}}}}');
				return $newNode;
			}
		}

		if (array_key_exists('override_class', $this->config) && $this->config['override_class'] !== null) {
			$new_classes = $this->config['override_class'];

			// If our configured `override_class` is _not_ an array, then treat it as
			// if it was a string, and explode it by the space character
			if (!is_array($new_classes)) {
				$new_classes = explode(" ", strval($new_classes));
			}

			// If we're preserving existing classes on the element …
			if (array_key_exists('preserve_classes', $this->config) && $this->config['preserve_classes']) {
				// … explode the existing class list …
				$existing_classes = explode(" ", $this->getAttribute($domNode, 'class'));
				// … and merge that with the new class list
				$new_classes = array_merge($new_classes, $existing_classes);
			}

			// Filter out empty values, and remove duplicates, leaving us with a
			// clean list of classes to apply to the target element
			$new_classes = array_unique(array_filter($new_classes));
			
			if (empty($new_classes)) {
				// If we have no classes, remove the class attribute - if we don't do
				// this, the classes that were already on the elemnet will remain, even
				// in the case of `preserve_classes` not being `true`
				$node->removeAttribute('class');
			} else {
				// But if we do have classes, set the element's class attribute
				$node->setAttribute('class', implode(" ", $new_classes));
			}
		}

		return $node;
	}
}
