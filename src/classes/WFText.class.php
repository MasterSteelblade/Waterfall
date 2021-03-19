<?php 

class WFText {

    public static function makeTextRenderable($content, $segmentID = 0) {
        /** Makes text be HTML formatted. Not necesary, but for safety.
        * @param content The content to make renderable.
        * @return result The renderable HMTL.
        */
        //$result =  str_replace('\;', ';', $result);
        $result = $content;
        //$result = imageReplace($result, $type);
        $result = str_replace('\"', '"', $result);
        $result = str_replace("\'", "'", $result);
        #$result = str_replace('"images/', '"https://'.$_ENV['SITE_URL'].'/images/', $result);
        #$result = str_replace('"../images/', '"https://'.$_ENV['SITE_URL'].'/images/', $result);
        $Parsedown = new Parsedown();
        $Parsedown->setSafeMode(true);
        $Parsedown->setBreaksEnabled(true);
        $result = $Parsedown->text($result);
        $result = WFText::mentionReplace($result);
        $result = WFText::imageReplace($result);
        if ($segmentID != 0) {
            $result = WFText::doReadMoreCheck($result, $segmentID);
        }
        $result = str_replace('img src', 'img class="img-fluid" src', $result);
        $result = str_replace('img  src', 'img class="img-fluid" src', $result);
        $result = str_replace('\\', '', $result);

        $emoji = new Emoji();
        #$result = $emoji->parseText($result);
        $result = str_replace('<pre>', '', $result);
        $result = str_replace('</pre>', '', $result);
        return $result;
    }

    public static function makeTextRenderableForEdit($content, $segmentID = 0) {
        /** Makes text be HTML formatted. Not necesary, but for safety.
        * @param content The content to make renderable.
        * @return result The renderable HMTL.
        */
        //$result =  str_replace('\;', ';', $result);
        $result = $content;
        //$result = imageReplace($result, $type);
        $result = str_replace('\"', '"', $result);
        $result = str_replace("\'", "'", $result);
        #$result = str_replace('"images/', '"https://'.$_ENV['SITE_URL'].'/images/', $result);
        #$result = str_replace('"../images/', '"https://'.$_ENV['SITE_URL'].'/images/', $result);
        $Parsedown = new Parsedown();
        $Parsedown->setSafeMode(true);
        $Parsedown->setBreaksEnabled(true);
        $result = $Parsedown->text($result);
        $result = WFText::mentionReplace($result);
        $result = WFText::imageReplace($result);
        $result = str_replace('{{READMORE}}', '<hr>', $result);

        $result = str_replace('img src', 'img class="img-fluid" src', $result);
        $result = str_replace('img  src', 'img class="img-fluid" src', $result);
        $result = str_replace('\\', '', $result);

        return $result;
    }
      


    public static function mentionReplace($content) {
        $postText = $content;
        preg_match_all('/{{MENTION:{{([0-9]+)}}}}/',$postText,$matches);
        $matches = end($matches);
        foreach ($matches as $match) {
            $blogID = $match;
            $blogID = ltrim($blogID, '{{MENTION:{{');
            $blogID = rtrim($blogID, '}}}}');
            $blog = new Blog($blogID);
            if (!isset($blog->failed)) {
                $blogName = $blog->blogName;
                $postText = str_replace('{{MENTION:{{'.$match.'}}}}', '<a href="https://'.$blogName.'.'.$_ENV['SITE_URL'].'" data-url-mentions="'.$blogName.'">@'.$blogName.'</a>', $postText);

            } else {
                $blogName = 'UnidentifiedBlog';
                $postText = str_replace('{{MENTION:{{'.$match.'}}}}', '<a href="https://staff.'.$_ENV['SITE_URL'].'" data-url-mentions="'.$blogName.'">@'.$blogName.'</a>', $postText);

            }
            $postText = str_replace('{{MENTION:{{'.$match.'}}}}', '<a href="https://'.$blogName.'.'.$_ENV['SITE_URL'].'" data-url-mentions="'.$blogName.'">@'.$blogName.'</a>', $postText);
        }


        return $postText;
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

      public static function makeTextSafe($content) {
        /** Makes text safe to store.
        *
        * @param content The content to make safe.
        * @return result The safe text.
        */
        $result = $content;
        $result = nl2br($result);

        $result = str_replace('<div>', '<p>', $result);
        $result = str_replace('</div>', '</p>', $result);
        $result = preg_replace('/\bon\w+=\S+(?=.*>)/', '', $result);
        $result = str_replace('<p><a href', '<div><a href', $result);
        $result = str_replace('</a></p>', '</a></div>', $result);
        $result = str_replace('("', '(   "', $result);
        $result = str_replace('")', '"   )', $result);
        // MCE
        $result =  preg_replace('/<hr \/>/', '{{READMORE}}', $result, 1);
        $result =  preg_replace('/<hr>/', '{{READMORE}}', $result, 1);
               $converter = new League\HTMLToMarkdown\HtmlConverter(array(
          'strip_tags' => true
        ));
        $result = $converter->convert($result);
        $result = str_replace("\_", "_", $result);
        $result = str_replace('("', '(', $result);
        $result = str_replace('")', ')', $result);
        $result = str_replace('"   )', '")', $result);
        $result = str_replace('(   "', '("', $result);
        $result = preg_replace("~(?:[\p{M}]{1})([\p{M}])~uis","", $result);

        $result = substr($result, 0, 35000);
        return $result;
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