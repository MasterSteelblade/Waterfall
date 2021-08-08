<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class WFTextMakeTextTest extends TestCase {
	public function testMakeTextStrippedReturnsOneLine() {
		$outputData = WFText::makeTextStripped(implode("\n", [
			"**Test strong**",
			"",
			"0. Test list item",
			"0. Test list item",
			"0. Test list item",
			"0. Test list item",
			"0. Test list item",
			"",
			"**Test strong**",
		]));

		$this->assertFalse(strpos($outputData, "\n"));
	}

	public function testMakeTextStrippedContainsNoHtml() {
		$outputData = WFText::makeTextStripped(implode("\n", [
			"**Test strong**",
			"",
			"0. Test list item",
			"0. Test list item",
			"0. Test list item",
			"0. Test list item",
			"0. Test list item",
			"",
			"**Test strong**",
		]));

		// If the above was rendered with HTML, we should expect at the least
		// a <ul> and some <li>, plus a <strong>. Check for those.
		$this->assertFalse(strpos($outputData, "<ul>"));
		$this->assertFalse(strpos($outputData, "<li>"));
		$this->assertFalse(strpos($outputData, "<strong>"));
	}
}
