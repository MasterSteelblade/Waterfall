<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use HtmlSanitizer\SanitizerBuilder;
use WFHtmlSanitizer\WFExtension;

final class WFHtmlSanitizerHrTest extends TestCase {
	public function testReadMoreShortcodingNormal() {
		// Construct the sanitizer
		$sanitizerBuilder = SanitizerBuilder::createDefault();
		$sanitizerBuilder->registerExtension(new WFExtension());
		$sanitizer = $sanitizerBuilder->build([
			'extensions' => ['waterfall'],
			'waterfall-shortcodes' => ['enabled' => ['readmore']],
		]);

		// Run the sanitizer
		$inputData = '<p>Test paragraph before read more</p><hr><p>Test paragraph after read more</p>';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertNotFalse(strpos($outputData, '{{READMORE}}'));
	}

	public function testReadMoreShortcodingClosingSlash() {
		// Construct the sanitizer
		$sanitizerBuilder = SanitizerBuilder::createDefault();
		$sanitizerBuilder->registerExtension(new WFExtension());
		$sanitizer = $sanitizerBuilder->build([
			'extensions' => ['waterfall'],
			'waterfall-shortcodes' => ['enabled' => ['readmore']],
		]);

		// Run the sanitizer
		$inputData = '<p>Test paragraph before read more</p><hr /><p>Test paragraph after read more</p>';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertNotFalse(strpos($outputData, '{{READMORE}}'));
	}
	
	public function testReadMoreDoesNotShortcodeWhenNotEnabled() {
		// Construct the sanitizer
		$sanitizerBuilder = SanitizerBuilder::createDefault();
		$sanitizerBuilder->registerExtension(new WFExtension());
		$sanitizer = $sanitizerBuilder->build([
			'extensions' => ['waterfall'],
			'waterfall-shortcodes' => ['enabled' => []],
		]);

		// Run the sanitizer
		$inputData = '<p>Test paragraph before read more</p><hr><p>Test paragraph after read more</p>';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertFalse(strpos($outputData, '{{READMORE}}'));
	}
}
