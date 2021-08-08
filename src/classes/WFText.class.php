<?php

use \HtmlSanitizer\SanitizerBuilder;
use \HtmlSanitizer\Sanitizer;
use \WFHtmlSanitizer\WFExtension;

class WFText {
	public static function createBaseSanitizer(array $options = []): Sanitizer {
		/**
		 * Creates a strict HtmlSanitizer for post content, optionally overriding
		 * the base configuration provided by this function with the passed-in
		 * options.
		 *
		 * @param options HtmlSanitizer option overrides
		 * @return A configured HtmlSanitizer
		 */

		$options = array_merge(
			// Defaults
			[
				'tags' => [
					'a' => [
						'allowed_attributes' => ['id', 'class', 'href'],
					],

					'img' => [
						'allowed_attributes' => ['id', 'class', 'src', 'alt', 'title'],
						'allowed_hosts' => [$_ENV['SITE_URL']],
						'override_class' => '',
						'preserve_classes' => false,
					],
				],
			],

			// Provided options after defaults
			$options,

			// Forceful override of provided options
			[
				'extensions' => [
					// Selected HtmlSanitizer defaults
					'basic', 'list', 'image', 'code', 'details', 'extra',

					// Waterfall extensions
					'waterfall',
				],
			],
		);

		$sanitizerBuilder = SanitizerBuilder::createDefault();
		$sanitizerBuilder->registerExtension(new WFExtension());
		return $sanitizerBuilder->build($options);
	}

	public static function createDefaultPostSanitizer(): Sanitizer {
		/**
		 * Create an HtmlSanitizer configured to validate and sanitize the Parsedown
		 * output from a post or page object that is already stored in the database.
		 *
		 * @return A configured HtmlSanitizer
		 */

		return self::createBaseSanitizer([
			'tags' => [
				'a' => [
					'allowed_attributes' => [
						// Standard stuff
						'href', 'id', 'class', 'width',

						// Mentions
						'data-url-mentions',

						// Lightbox stuff
						'data-caption', 'data-fancybox',
					 ],
				],

				'img' => [
					'allowed_attributes' => ['src', 'alt', 'title', 'class', 'data-image-id'],

					// Only allow images loaded from our own servers to pass through the filter
					'allowed_hosts' => [$_ENV['SITE_URL']],

					// Always use HTTPS
					'force_https' => true,

					// Always add the `img-fluid` class to images
					'override_class' => "img-fluid",

					// Preserve the classes already on the image
					'preserve_classes' => true,
				],
			],
		]);
	}

	public static function createUntrustedPostSanitizer(): Sanitizer {
		/**
		 * Create an HtmlSanitizer configured to sanitize the living daylights out
		 * of untrusted input submitted via the post/page creation or edit forms.
		 * This configuration allows the bare minimum through the filter, and should
		 * ALWAYS be used for untrusted user-provided content.
		 *
		 * @return A configured HtmlSanitizer
		 */

		return self::createBaseSanitizer([
			'waterfall-shortcodes' => [
				'enabled' => [
					'image', 'mention', 'readmore',
				],
			],

			'tags' => [
				'a' => [
					'allowed_attributes' => ['href', 'title'],
				],

				'img' => [
					'allowed_attributes' => ['src', 'alt', 'title'],

					// Only allow images loaded from our own servers to pass through the
					// filter, and always force loading those images over HTTPS
					'allowed_hosts' => [$_ENV['SITE_URL']],
					'force_https' => true,

					// Strip all provided classes from image elements
					'override_class' => '',
					'preserve_classes' => false,
				],
			],
		]);
	}

	public static function createUntrustedContentSanitizer(): Sanitizer {
		/**
		 * Create an HtmlSanitizer configured to sanitize the living daylights out
		 * of untrusted input. This sanitizer should always be used for untrusted
		 * input that is not being used for blog post/page content.
		 *
		 * @return A configured HtmlSanitizer
		 */

		return self::createBaseSanitizer([
			'tags' => [
				'a' => [
					'allowed_attributes' => ['href', 'title'],
				],

				'img' => [
					'allowed_attributes' => ['src', 'alt', 'title'],

					// Only allow images loaded from our own servers to pass through the
					// filter, and always force loading those images over HTTPS
					'allowed_hosts' => [$_ENV['SITE_URL']],
					'force_https' => true,

					// Strip all provided classes from image elements
					'override_class' => '',
					'preserve_classes' => false,
				],
			],
		]);
	}

	public static function makeTextRenderable($content, $segmentID = 0) {
		/**
		 * Makes the text of a post segment (or blog page) renderable by the UI,
		 * including HTML sanitization.
		 *
		 * @param content The content to make renderable.
		 * @return The renderable HTML.
		 */

		// Create a Parsedown instance
		$parsedown = (new Parsedown())->setSafeMode(true)->setBreaksEnabled(true);

		// Create an HtmlSanitizer
		$sanitizer = self::createDefaultPostSanitizer();

		// Run Parsedown on the input
		$content = $parsedown->text($content);

		// Replace mentions and images
		$content = self::shorttagMentionRender($content);
		$content = self::shorttagImageRender($content);

		// Run the HTML sanitizer
		//
		// Note that this should be done AFTER the call to `WFText::imageReplace`
		// but BEFORE the read-more and emoji checks
		$content = $sanitizer->sanitize($content);

		// Replace emoji
		//$emoji = new Emoji();
		//$content = $emoji->parseText($content);

		// Do the read-more check
		if ($segmentID != 0) {
			$content = self::shorttagReadMoreRender($content, $segmentID);
		}

		return $content;
    }

    public static function makeTextRenderableForEdit($content, $segmentID = 0) {
		/**
		 * Makes the text of a post segment (or blog page) renderable by the
		 * post/page text editor, including HTML sanitization.
		 *
		 * @param content The content to make renderable.
		 * @return The renderable HTML.
		 */

		// Create a Parsedown instance
		$parsedown = (new Parsedown())->setSafeMode(true)->setBreaksEnabled(true);

		// Create an HtmlSanitizer
		$sanitizer = self::createDefaultPostSanitizer();

		// Run Parsedown on the input
		$content = $parsedown->text($content);

		// Replace mentions and images
		$content = self::shorttagMentionRender($content);
		$content = self::shorttagImageRender($content);

		// Run the HTML sanitizer
		//
		// Note that this should be done AFTER the call to `WFText::imageReplace`
		// but BEFORE the read-more <hr> replacement below
		$content = $sanitizer->sanitize($content);

		// Replace read-more with a <hr>
		$content = str_replace('{{READMORE}}', '<hr>', $content);

		return $content;
    }

    public static function makeTextStripped($content, $segmentID = 0) {
		/**
		 * Renders a stripped text-only version of the text of a post/page,
		 * suitable for use in things like Open Graph description tags.
		 *
		 * @param content The content to make renderable.
		 * @return The rendered plain text.
		 */

		// Run Parsedown on the input
		$parsedown = (new Parsedown())->setSafeMode(true)->setBreaksEnabled(true);
		$content = $parsedown->text($content);

        // Strip out all HTML.
        //
        // HtmlSanitizer at it's default settings (with no extensions) will
        // strip *everything*, which is what we want.
        $sanitizer = \HtmlSanitizer\Sanitizer::create(['extensions' => []]);
        $content = $sanitizer->sanitize($content);

		// Replace all linebreaks with a single space.
		$content = str_replace("\n", " ", $content);

        return $content;
    }

	public static function makeTextSafe($content) {
		/**
		 * Makes the input content "safe" for rendering in most places in the user
		 * interface, by stripping literally everything from it.
		 *
		 * Do not use this for post or page content, or anything that should retain
		 * markup (use the `makeTextPostContentSafe` function for that) - because
		 * everything you love about the input markup will be gone from the return
		 * value.
		 *
		 * @param content The content to make "safe"
		 * @return The "safe" text
		 */

		// Strip out all HTML.
		//
		// HtmlSanitizer at it's default settings (with no extensions) will
		// strip *everything*, which is what we want.
		$sanitizer = \HtmlSanitizer\Sanitizer::create(['extensions' => []]);
		$content = $sanitizer->sanitize($content);

		// Yeet the Zalgo text into the sun, hopefully.
		$content = preg_replace("~(?:[\p{M}]{1})([\p{M}])~uis", "", $content);

		return $content;
	}

	public static function makeTextPostContentSafe($content) {
		/**
		 * Sanitizes the input HTML content (which is assumed to be untrusted, as it
		 * is provided directly to us by the user's browser), and converts it to a
		 * format suitable for storing in the database as the content of a post or
		 * blog page.
		 *
		 * @param content Untrusted input HTML
		 * @return Safe Markdown-formatted content for storage
		 */

		// Create an HTML to Markdown converter
		$converter = new \League\HTMLToMarkdown\HtmlConverter([
			'strip_tags' => true,
		]);

		// Create an "untrusted content" HTML sanitizer
		$sanitizer = self::createUntrustedPostSanitizer();

		// Convert line breaks to <br> tags
		$content = nl2br($content);

		// Run through the sanitizer.
		//
		// This will automatically convert the following to shortcodes:
		//   - <img> tags pointing to site-hosted images
		//   - <a> tags that are blog @mentions
		//   - <hr> tags (to a read-more)
		$content = $sanitizer->sanitize($content);

		// Convert the sanitized HTML to Markdown
		$content = $converter->convert($content);

		// Yeet the Zalgo text into the sun, hopefully.
		$content = preg_replace("~(?:[\p{M}]{1})([\p{M}])~uis", "", $content);

		// And we're done!
		return $content;
	}

	public static function shorttagMentionRender($content) {
		/**
		 * Replace all MENTION short-tags in the content with a link to the blog
		 * being mentioned.
		 *
		 * @param content The content to search and modify
		 * @return The modified content
		 */

		// Turn on "user error handling" for LibXML, storing the old value so we
		// can flip it back at the end of the function
		$libxmlPreviousErrorMode = libxml_use_internal_errors(true);

		$replacements = array();

		$match_regex = '|{{MENTION:{{([0-9]+)}}}}|';
		if (preg_match($match_regex, $content, $matches)) {
			foreach ($matches as $i => $match) {
				if ($i == 0) continue;

				$blogID = intval($match) ?? 0;
				$blog = new Blog($blogID);

				// If we have a valid blog, add an entry to the `replacements` array
				if ($blogID > 0 && isset($blog) && !$blog->failed) {
					$replacements[strval($blogID)] = $blog;
				}
			}
		}

		// For each entry in the `replacements` array …
		foreach ($replacements as $strBlogID => $blog) {
			// … construct a link to the mentioned blog …
			$mentionDoc = new \DOMDocument("1.0");
			$mentionDoc->encoding = 'UTF-8';
			$mentionLink = $mentionDoc->createElement('a');
			$mentionDoc->appendChild($mentionLink);
			$mentionLink->nodeValue = "@{$blog->blogName}";
			$mentionLink->setAttribute('href', $blog->getBlogURL());
			$mentionLink->setAttribute('data-url-mentions', $blog->blogName);

			// … dump that link element as XML …
			$replacement_text = $mentionDoc->saveXML($mentionLink);

			// … create a pattern to do the replacement with …
			$replacement_pattern = '{{MENTION:{{' . strval(intval($strBlogID)) . '}}}}';

			// … and replace all occurrences in our `$content` with the link
			$content = str_replace($replacement_pattern, $replacement_text, $content);
		}

		// Now, we want to scan for any MENTION short-tags left over (which will be
		// the result of an invalid blog), and replace them with a mention link
		// pointing to `unidentified-blog`.
		if (preg_match($match_regex, $content, $matches)) {
			// Construct a link to the `unidentified-blog` …
			$mentionDoc = new \DOMDocument("1.0");
			$mentionDoc->encoding = 'UTF-8';
			$mentionLink = $mentionDoc->createElement('a');
			$mentionDoc->appendChild($mentionLink);
			$mentionLink->nodeValue = "@unidentified-blog";
			$mentionLink->setAttribute('href', "https://unidentified-blog." . $_ENV['SITE_URL'] . "/");
			$mentionLink->setAttribute('data-url-mentions', 'unidentified-blog');

			// … dump that link element as XML …
			$replacement_text = $mentionDoc->saveXML($mentionLink);

			// … iterate over our matches …
			foreach ($matches as $i => $match) {
				if ($i == 0) continue;

				// … create a pattern to do the replacement with …
				$replacement_pattern = '{{MENTION:{{' . $match . '}}}}';

				// … and replace all occurrences in our `$content` with the link
				$content = str_replace($replacement_pattern, $replacement_text, $content);
			}
		}

		// Reset the "user error handling" flag of LibXML.
		libxml_use_internal_errors($libxmlPreviousErrorMode);

		// And with that, we're done!
		return $content;
	}

	public static function shorttagReadMoreRender($postContent, $segmentID) {
		/**
		 * Searches for a READMORE shortcode within the content of the post, adding
		 * the toggle button and putting everything after the READMORE shortcode
		 * within a container element. Does nothing if there is not a READMORE
		 * shortcode in the post content.
		 *
		 * @param content The content to search and modify
		 * @return The modified content
		 */

		// Turn on "user error handling" for LibXML.
		//
		// Practically, this lets us ignore the (non-fatal) warnings that LibXML
		// spews when parsing slightly-malformed HTML - like, when there's an end
		// tag but not a start tag. These errors occur all the time with data that
		// comes from users, so let's just ignore the bloody things.
		//
		// Storing the value of this so it can be put back to it's old value when
		// we're done here.
		$libxmlPreviousErrorMode = libxml_use_internal_errors(true);

		if (strpos($postContent, '{{READMORE}}') !== false) {
			// Create an instance of the default sanitizer
			$sanitizer = self::createDefaultPostSanitizer();

			// Split the post content, so that everything BEFORE the read-more marker
			// is in `$contentPre`, and everything AFTER is in `$contentPost` - this
			// helpfully excludes the marker shortcode itself.
			list($contentPre, $contentPost) = explode("{{READMORE}}", $postContent, 2);

			// Create a DOMDocument from a normal sanitize of the `$contentPost` section
			$contentPreDoc = new \DOMDocument();
			$contentPreDoc->encoding = 'UTF-8';
			$contentPreDoc->loadHTML(
				$sanitizer->sanitize($contentPre),
				LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOXMLDECL | LIBXML_NOWARNING,
			);

			// Create a DOMDocument from a normal sanitize of the `$contentPost` section
			$contentPostDoc = new \DOMDocument();
			$contentPostDoc->encoding = 'UTF-8';
			$contentPostDoc->loadHTML(
				$sanitizer->sanitize($contentPost),
				LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOXMLDECL | LIBXML_NOWARNING,
			);

			// Create the DOMDocument to use to construct the show/hide button and
			// the read-more content container
			$readMoreDoc = new \DOMDocument("1.0");
			$readMoreDoc->encoding = 'UTF-8';

			// Construct the button
			$buttonElement = $readMoreDoc->createElement('button');
			$readMoreDoc->appendChild($buttonElement);
			$buttonElement->nodeValue = \L::string_read_more;
			$buttonElement->setAttribute('class', "btn btn-light btn-sm btn-block");
			$buttonElement->setAttribute('type', 'button');
			$buttonElement->setAttribute('data-toggle', 'collapse');
			$buttonElement->setAttribute('data-target', "#postReadMore{$segmentID}");
			$buttonElement->setAttribute('aria-expanded', 'false');
			$buttonElement->setAttribute('aria-controls', "postReadMore{$segmentID}");

			// Construct the read more container
			$containerElement = $readMoreDoc->createElement('div');
			$readMoreDoc->appendChild($containerElement);
			$containerElement->setAttribute('id', "postReadMore{$segmentID}");
			$containerElement->setAttribute('class', 'collapse');

			// Add the child nodes from `$contentPostDoc` into the `$containerElement`
			foreach ($contentPostDoc->childNodes as $cid => $child) {
				$childElement = $contentPostDoc->removeChild($child);
				$containerElement->appendChild($readMoreDoc->importNode($childElement, true));
			}

			// Set `$postContent` to the combination of `$contentPre` (which is
			// everything BEFORE the read-more marker) and the HTML dump of the
			// `$readMoreDoc` (which contains the show/hide button and the container
			// that holds everything AFTER the read-more marker).
			$postContent = implode("\n", [
				$contentPreDoc->saveHTML(),
				$readMoreDoc->saveHTML(),
			]);
		}

		// Reset the "user error handling" flag of LibXML.
		libxml_use_internal_errors($libxmlPreviousErrorMode);

		return $postContent;
	}

	public static function shorttagImageRender($content) {
		/**
		 * Replace all IMAGE short-tags in the content with the embedded image.
		 *
		 * @param content The content to search and modify
		 * @return The modified content
		 */

		// Turn on "user error handling" for LibXML, storing the old value so we
		// can flip it back at the end of the function
		$libxmlPreviousErrorMode = libxml_use_internal_errors(true);

		// Device detection, for choosing a resolution
		$deviceDetect = new \Mobile_Detect();
		$deviceType = "desktop";
		if ($deviceDetect->isMobile()) {
			$deviceType = "mobile";
		} elseif ($deviceDetect->isTablet()) {
			$deviceType = "tablet";
		}

		// Initialise the replacement data array
		$replacements = array();

		// Start matching
		$match_regex = '|{{IMAGE:{{([0-9]+)}}}}|';
		if (preg_match($match_regex, $content, $matches)) {
			foreach ($matches as $i => $match) {
				if ($i == 0) continue;

				$imageID = intval($match) ?? 0;
				$replacements[strval($imageID)] = new WFImage($imageID);
			}
		}

		// For each entry in the `replacements` array …
		foreach ($replacements as $strImageID => $image) {
			// … grab an ID for the lightbox …
			$rid = WFUtils::generateRandomString(12);

			// … construct an image container …
			$imageDoc = new \DOMDocument("1.0");
			$imageDoc->encoding = 'UTF-8';

			// … a link to the image …
			$imageLink = $imageDoc->createElement('a');
			$imageDoc->appendChild($imageLink);
			$imageLink->setAttribute('class', 'mx-auto');
			$imageLink->setAttribute('href', $image->getPath('full'));
			$imageLink->setAttribute('width', $image->getDimension('width'));
			$imageLink->setAttribute('data-caption', $image->getCaption());
			$imageLink->setAttribute('data-fancybox', $rid);

			// … and the image element …
			$imageElement = $imageDoc->createElement('img');
			$imageLink->appendChild($imageElement);
			$imageElement->setAttribute('class', "mx-auto img-fluid wf-imagetype-{$deviceType}");
			$imageElement->setAttribute('src', $image->getPath($deviceType));
			$imageElement->setAttribute('width', $image->getDimension('width'));
			$imageElement->setAttribute('title', $image->data['caption']);
			$imageElement->setAttribute('alt', $image->data['description']);
			$imageElement->setAttribute('data-image-id', $image->ID);

			// … dump the link (containing the image element) as XML …
			$replacement_text = $imageDoc->saveXML($imageLink);

			// … create a pattern to do the replacement with …
			$replacement_pattern = '{{IMAGE:{{' . strval(intval($strImageID)) . '}}}}';

			// … and replace all occurrences in our `$content` with the link
			$content = str_replace($replacement_pattern, $replacement_text, $content);
		}

		// Reset the "user error handling" flag of LibXML.
		libxml_use_internal_errors($libxmlPreviousErrorMode);

		return $content;
	}

	public static function getInlines($text) {
		$database = Postgres::getInstance();
		$htmlDom = new DOMDocument();
		$htmlDom->loadHTML('<?xml encoding="utf-8" ?>'.$text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		$htmlDomRef = new DOMDocument();
		$htmlDomRef->loadHTML('<?xml encoding="utf-8" ?>'.$text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		$imageTags = $htmlDom->getElementsByTagName('img');
		$inlineImageIDs = array();
		$imageRefs = $htmlDomRef->getElementsByTagName('img');

		foreach($imageRefs as $key => $imageTag){
			$extractedImage = $imageTag->getAttribute('src');
			if (base64_decode($extractedImage) !== false) {
				$randStr = WFUtils::generateRandomString(6);
				file_put_contents('/tmp/phpfilepostimg'.$randStr, file_get_contents($extractedImage));
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$server = WFUtils::pickServer();
				$url = $server.'/image/add';
				curl_setopt($ch, CURLOPT_URL, $url);
				$postData = array();
				$postData['images'] = new CurlFile("/tmp/phpfilepostimg".$randStr);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
				curl_setopt($ch,CURLOPT_TIMEOUT,100);
				$chResponse = curl_exec($ch);
				$json = json_decode($chResponse, true);
				unset($data);
				$data = array();
				if (isset($json['imgData'])) {
					$data = $json['imgData'];
					$onServer = array($json['onServer']);
					$values = array(json_encode($data), 'f', $database->php_to_postgres($onServer));
					$imageID = $database->db_insert("INSERT INTO images (paths, is_art, servers, version) VALUES ($1,$2,$3,2)", $values);
				} else {
					$imageID = 0;
					$failedImages[] = $extractedImage;
                }

				$inlineImageIDs[] = $imageID;
				$imageText = $htmlDom->createTextNode('{{IMAGE:{{'.$imageID.'}}}}');
				$imageTags[0]->parentNode->replaceChild($imageText, $imageTags[0]);

			} elseif ($imageTag->hasAttribute('data-image-id')) {
				$imageID = $imageTag->getAttribute('data-image-id');
				$inlineImageIDs[] = $imageID;
				$imageText = $htmlDom->createTextNode('{{IMAGE:{{'.$imageID.'}}}}');
				$imageTags[0]->parentNode->replaceChild($imageText, $imageTags[0]);
			}
		}

		// Remove hack
		foreach ($htmlDom->childNodes as $item) {
			if ($item->nodeType == XML_PI_NODE) {
				$htmlDom->removeChild($item);
			}
		}

		// Insert proper
		$htmlDom->encoding = 'UTF-8';
		$text = $htmlDom->saveHTML();

		return array($text, $inlineImageIDs);
    }

	public static function is_base64($s) {
		return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]+=*$/', $s);
	}
}
