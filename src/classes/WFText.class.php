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
		$content = self::imageReplace($content);

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
			$content = self::doReadMoreCheck($content, $segmentID);
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
		$content = self::imageReplace($content);

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

    public static function shorttagMentionRender($content) {
		/**
		 * Replace all MENTION short-tags in the content with a link to the blog
		 * being mentioned.
		 *
		 * @param content The content to search and modify
		 * @return The modified content
		 */

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

		// And with that, we're done!
		return $content;
	}

    public static function imageReplace($postText) {
        $detect = new Mobile_Detect;
        if ( $detect->isMobile() ) {
            $type = 'mobile';
        } elseif ($detect->isTablet()) {
            $type = 'tablet';
        } else {
            $type = 'desktop';
        }
        preg_match_all('/{{IMAGE:{{([0-9]+)}}}}/',$postText,$matches);
        $matches = end($matches);
        foreach ($matches as $match) {
            
            $imageID = $match;
            $imageID = ltrim($imageID, '{{IMAGE:{{');
            $imageID = rtrim($imageID, '}}}}');
            $img1 = new WFImage($imageID);
            $width = $img1->getDimension('width');

            $rid = WFUtils::generateRandomString(12);

            if ($width < 810) { // CHECK LATER
                $str = '<a class="mx-auto" data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" width="'.$width.'" href="'.$img1->getPath('full').'"><img class="mx-auto img-fluid" width="'.$width.'" data-image-id="'.$img1->ID.'" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath('full').'"></a>';
            } else {
                $str = '<a data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" href="'.$img1->getPath('full').'"><img class="img-fluid w-100" data-image-id="'.$img1->ID.'" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath($type).'"></a>';
            }
            $postText = str_replace('{{IMAGE:{{'.$match.'}}}}', $str, $postText);
        }
        return $postText;

    }

    public static function doReadMoreCheck($postContent, $segmentID) {
        if (strpos($postContent, '{{READMORE}}') !== false) {
          $postContent = str_replace('{{READMORE}}', '<button class="btn btn-light btn-sm btn-block" type="button" data-toggle="collapse" data-target="#postSeg'.$segmentID.'" aria-expanded="false" aria-controls="'.$segmentID.'">
          Read More
        </button><div class="collapse" id="postSeg'.$segmentID.'">', $postContent);
        $postContent = $postContent.'</div>';
        }
        return $postContent;
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
        foreach ($htmlDom->childNodes as $item)
            if ($item->nodeType == XML_PI_NODE)
                $htmlDom->removeChild($item); // remove hack
        $htmlDom->encoding = 'UTF-8'; // insert proper
        $text = $htmlDom->saveHTML();

    
    return array($text, $inlineImageIDs);
    }

    public static function is_base64($s)
    {
          return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
    }



}