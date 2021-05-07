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

	public bool $containShortcode = false;
	public function setContainShortcode(bool $containShortcode) {
		$this->containShortcode = $containShortcode;
	}

	public function render(): string {
		if ($this->containShortcode) {
			return "<p>{$this->shortcode}</p>";
		}

		return $this->shortcode;
	}
}
