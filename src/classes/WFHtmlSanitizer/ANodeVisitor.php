<?php

namespace WFHtmlSanitizer;

use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\NodeInterface;
use HtmlSanitizer\Visitor\AbstractNodeVisitor;
use HtmlSanitizer\Visitor\NamedNodeVisitorInterface;
use HtmlSanitizer\Visitor\HasChildrenNodeVisitorTrait;
use HtmlSanitizer\Extension\Basic\Node\ANode;

class ANodeVisitor extends AbstractNodeVisitor implements NamedNodeVisitorInterface {
	use HasChildrenNodeVisitorTrait;

	protected function getDomNodeName(): string {
		return 'a';
	}

	protected function createNode(\DOMNode $domNode, Cursor $cursor): NodeInterface {
		$node = new ANode($cursor->node);

		// Do we have mention shortcoding enabled?
		if (array_key_exists('enabled', $this->config['waterfall-shortcodes']) && in_array('mention', $this->config['waterfall-shortcodes']['enabled'] ?? [])) {
			// Does this image have a `data-url-mentions` attribute?
			if (!empty($blogName = $this->getAttribute($domNode, 'data-url-mentions'))) {
				// Yes it does, let's grab the blog from the name
				$blog = new \Blog();
				$blog->getByBlogName($blogName);
				if (!$blog->failed) {
					// Cool, we have a blog object! Let's do the shortcoding.
					$newNode = new ShortcodeNode($cursor->node);
					$newNode->setShortcode('{{MENTION:{{' . $blog->ID . '}}}}');
					return $newNode;
				}
			}
		}

		return $node;
	}
}
