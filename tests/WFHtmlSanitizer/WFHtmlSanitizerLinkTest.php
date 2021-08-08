<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use HtmlSanitizer\SanitizerBuilder;
use WFHtmlSanitizer\WFExtension;

final class WFHtmlSanitizerLinkTest extends TestCase {
	public function testMentionShortcoding() {
		// Construct the sanitizer
		$sanitizerBuilder = SanitizerBuilder::createDefault();
		$sanitizerBuilder->registerExtension(new WFExtension());
		$sanitizer = $sanitizerBuilder->build([
			'extensions' => ['waterfall'],
			'waterfall-shortcodes' => ['enabled' => ['mention']],
		]);

		// Run the sanitizer
		$inputData = '<a data-url-mentions="test" href="https://test.waterfall.test">@test</a>';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertNotFalse(strpos($outputData, '{{MENTION:{{'));
	}

	public function testImageDoesNotShortcodeWhenNotEnabled() {
		// Construct the sanitizer
		$sanitizerBuilder = SanitizerBuilder::createDefault();
		$sanitizerBuilder->registerExtension(new WFExtension());
		$sanitizer = $sanitizerBuilder->build([
			'extensions' => ['waterfall'],
			'waterfall-shortcodes' => ['enabled' => []],
		]);

		// Run the sanitizer
		$inputData = '<a data-url-mentions="test" href="https://test.waterfall.test">@test</a>';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertFalse(strpos($outputData, '{{MENTION:{{'));
	}
	
	public function testImageDoesNotShortcodeWithoutUrlMentions() {
		// Construct the sanitizer
		$sanitizerBuilder = SanitizerBuilder::createDefault();
		$sanitizerBuilder->registerExtension(new WFExtension());
		$sanitizer = $sanitizerBuilder->build([
			'extensions' => ['waterfall'],
			'waterfall-shortcodes' => ['enabled' => ['image']],
		]);

		// Run the sanitizer
		$inputData = '<a href="https://test.waterfall.test">@test</a>';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertFalse(strpos($outputData, '{{MENTION:{{'));
	}
}
