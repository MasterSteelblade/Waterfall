<?php

namespace WFHtmlSanitizer;

use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\NodeInterface;
use HtmlSanitizer\Visitor\AbstractNodeVisitor;
use HtmlSanitizer\Visitor\NamedNodeVisitorInterface;
use HtmlSanitizer\Visitor\IsChildlessTagVisitorTrait;
use HtmlSanitizer\Extension\Extra\Node\HrNode;

class HrNodeVisitor extends AbstractNodeVisitor implements NamedNodeVisitorInterface {
	use IsChildlessTagVisitorTrait;

	protected function getDomNodeName(): string {
		return 'hr';
	}

	protected function createNode(\DOMNode $domNode, Cursor $cursor): NodeInterface {
		$node = new HrNode($cursor->node);

		// Do we have read-more shortcoding enabled?
		if (array_key_exists('enabled', $this->config['waterfall-shortcodes']) && in_array('readmore', $this->config['waterfall-shortcodes']['enabled'] ?? [])) {
			// Yes we do, let's shortcode it
			$newNode = new ShortcodeNode($cursor->node);
			$newNode->setShortcode('{{READMORE}}');
			return $newNode;
		}

		return $node;
	}
}
