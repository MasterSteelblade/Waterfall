<?php
declare(strict_types=1);
require_once(dirname(dirname(__DIR__)) . "/vendor/autoload.php");

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
		$inputData = '<hr>';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertEquals($outputData, '{{READMORE}}');
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
		$inputData = '<hr />';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertEquals($outputData, '{{READMORE}}');
	}
}
