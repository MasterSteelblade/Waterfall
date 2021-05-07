<?php

namespace WFHtmlSanitizer;

use HtmlSanitizer\Node\AbstractTagNode;
use HtmlSanitizer\Node\HasChildrenTrait;

class ANode extends AbstractTagNode {
	use HasChildrenTrait;

	public function getTagName(): string {
		return 'a';
	}

	public function render(): string {
		$tag = $this->getTagName();

		// XXX: Return an empty string if we have no children and no content.
		// 
		// This is a bit of a hack to make sure that existing post/page content
		// continues to render correctly with the combination of the new WFText
		// setup, and the new WFHtmlSanitizer.
		if (empty($renderedChildren = trim($this->renderChildren()))) {
			return '';
		}

		return "<{$tag}{$this->renderAttributes()}>{$renderedChildren}</{$tag}>";
	}
}
