<?php
declare(strict_types=1);
require_once(dirname(dirname(__DIR__)) . "/vendor/autoload.php");

use PHPUnit\Framework\TestCase;
use HtmlSanitizer\SanitizerBuilder;
use WFHtmlSanitizer\WFExtension;

final class WFHtmlSanitizerImageTest extends TestCase {
	public function testImageShortcoding() {
		// Construct the sanitizer
		$sanitizerBuilder = SanitizerBuilder::createDefault();
		$sanitizerBuilder->registerExtension(new WFExtension());
		$sanitizer = $sanitizerBuilder->build([
			'extensions' => ['waterfall'],
			'waterfall-shortcodes' => ['enabled' => ['image']],
		]);

		// Run the sanitizer
		$inputData = '<img class="img-fluid mx-auto" data-image-id="11" src="https://01.media.waterfall.test/images/00/wfraven_00_1280.webp">';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertEquals($outputData, '{{IMAGE:{{11}}}}');
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
		$inputData = '<img class="img-fluid mx-auto" data-image-id="11" src="https://01.media.waterfall.test/images/00/wfraven_00_1280.webp">';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertFalse(strpos($outputData, '{{IMAGE:{{'));
	}
	
	public function testImageDoesNotShortcodeWithoutImageID() {
		// Construct the sanitizer
		$sanitizerBuilder = SanitizerBuilder::createDefault();
		$sanitizerBuilder->registerExtension(new WFExtension());
		$sanitizer = $sanitizerBuilder->build([
			'extensions' => ['waterfall'],
			'waterfall-shortcodes' => ['enabled' => ['image']],
		]);

		// Run the sanitizer
		$inputData = '<img class="img-fluid mx-auto" src="https://01.media.waterfall.test/images/00/wfraven_00_1280.webp">';
		$outputData = $sanitizer->sanitize($inputData);

		// Verify the output
		$this->assertFalse(strpos($outputData, '{{IMAGE:{{'));
	}
}
