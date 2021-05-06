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

		if ($this->config['override_class'] !== null) {
			$new_classes = explode(" ", $this->config['override_class']);
			
			if ($this->config['preserve_classes'] === true) {
				$existing_classes = explode(" ", $this->getAttribute($domNode, 'class'));
				$new_classes = array_unique(array_merge($new_classes, $existing_classes));
			}

			$node->setAttribute('class', implode(" ", $new_classes));
		}

		return $node;
	}
}
