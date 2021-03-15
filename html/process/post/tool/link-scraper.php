<?php

require_once(__DIR__.'/../../../includes/session.php');
header('Content-type: application/json');

$data = array();

if (isset($_POST['url'])) {
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        // Valid, get metadata
        $pageData = file_get_contents($url);
        $metaTags = \ogp\Parser::parse($pageData);
        if (isset($metaTags['og']['og:title'])) {
            $pageTitle = $metaTags['og']['og:title'];
          } elseif (isset($metaTags['twitter']['twitter:title'])) {
            $pageTitle = $metaTags['twitter']['twitter:title'];
          } elseif (isset($metaTags['title'])) {
            $pageTitle = $metaTags['title'];
          } else { 
            $pageTitle = null; 
          }
      
          if (isset($metaTags['og']['og:description'])) {
            $description = $metaTags['og']['og:description'];
          } elseif (isset($metaTags['twitter']['twitter:description'])){
            $description = $metaTags['twitter']['twitter:description'];
      
          } elseif (isset($metaTags['description'])) {
            $description = $metaTags['description'];
      
          } else {
              $description = null;
          }
            if (isset($metaTags['og']['og:image'])) {
      
            $image = $metaTags['og']['og:image'];
          } elseif (isset($metaTags['twitter']['twitter:image'])) {
            $image =  $metaTags['twitter']['twitter:image'];
          } else {
            $image = null;
          }
          if ($image != '') {
            $image = filter_var($image, FILTER_SANITIZE_URL);
            $image = filter_var(urldecode($image), FILTER_SANITIZE_SPECIAL_CHARS);
            if (filter_var($image, FILTER_VALIDATE_URL) && (parse_url($image, PHP_URL_SCHEME) == 'http' || parse_url($image, PHP_URL_SCHEME) == 'https')) {
              // Do nothing because it's fine
            } else {
              $image = '';
              // Not fine, blank it out
            }
          }
          $data['code'] = 'SUCCESS';
          $data['message'] = "Success";
          $data['title'] = $pageTitle;
          $data['description'] = $description; 
          $data['imageURL'] = $image;
          echo json_encode($data);

    } else {
        $data['code'] = 'INVALID_URL';
        $data['message'] = "Invalid URL";
        echo json_encode($data);
        exit();
    }
} else {
    $data['code'] = 'NO_URL';
    $data['message'] = "No URL";
    echo json_encode($data);

}