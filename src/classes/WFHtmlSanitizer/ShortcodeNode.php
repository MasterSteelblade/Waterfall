<?php

namespace WFHtmlSanitizer;

use HtmlSanitizer\Node\AbstractNode;
use HtmlSanitizer\Node\IsChildlessTrait;

class ShortcodeNode extends AbstractNode
{
	use IsChildlessTrait;

	public string $shortcode;
	public function setShortcode(string $shortcode) {
		$this->shortcode = $shortcode;
	}
	
	public function render(): string {
		return $this->shortcode;
	}
}
