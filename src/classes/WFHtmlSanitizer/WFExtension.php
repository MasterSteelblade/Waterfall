<?php

/**
 * Waterfall extensions to HtmlSanitizer.
 *
 * To use this extension, register it with a \HtmlSanitizer\SanitizerBuilder
 * instance, and then pass `'waterfall'` as part of the `'extensions'` array
 * when performing the HtmlSanitizer build:
 *
 * ```php
 * $sanitizerBuilder = \HtmlSanitizer\SanitizerBuilder::createDefault();
 * $sanitizerBuilder->registerExtension(new \WFHtmlSanitizer\WFExtension());
 *
 * $sanitizer = $sanitizerBuilder->build(['extensions' => [..., 'waterfall']]);
 * ```
 */

namespace WFHtmlSanitizer;

use HtmlSanitizer\Extension\ExtensionInterface;

class WFExtension implements ExtensionInterface {
	public function getName(): string {
		return 'waterfall';
	}

	public function createNodeVisitors(array $config = []): array {
		return [
			'img' => new ImgNodeVisitor(array_merge($config['tags']['img'] ?? [], ['waterfall-shortcodes' => $config['waterfall-shortcodes'] ?? []])),
			'hr' => new HrNodeVisitor(array_merge($config['tags']['hr'] ?? [], ['waterfall-shortcodes' => $config['waterfall-shortcodes'] ?? []])),
		];
	}
}
