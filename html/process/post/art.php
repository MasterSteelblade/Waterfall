<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }
    return $file_ary;
}


if ($session == false) {
    $data['code'] = 'NO_SESSION';
    $data['message'] = L::error_no_session;
    echo json_encode($data);
    exit();
}


$blog = new Blog();
$blog->getByBlogName($_POST['onBlog']);
if ($blog->failed || ($blog->ownerID != $sessionObj->user->ID && !$blog->checkMemberPermission($sessionObj->user->ID, 'write_post'))) {
    $data['code'] = 'ERR_NOT_YOUR_BLOG';
    $data['message'] = L::error_invalid_permissions;

    echo json_encode($data);
    exit();
}

use Spatie\Async\Pool;

$pool = Pool::create();

// Do files here. 
$files = reArrayFiles($_FILES['image']);

if (count($files) == 0) {
    $data['code'] = 'ERR_NO_IMAGES';
    $data['message'] = L::error_no_images;
    echo json_encode($data);
    exit();
}
// Files are now organised in a somewhat sane manner. 
$outputJSONs = array();
$originalMD5 = array();
foreach ($files as $file) {
    $pool->add(function () use ($file) {
        $randStr = WFUtils::generateRandomString(6);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $server = WFUtils::pickServer();
        $url = $server.'/image/add';
        curl_setopt($ch, CURLOPT_URL, $url);
        $postData = array();
        $postData['images'] = new CurlFile($file['tmp_name']);
        $postData['isArt'] = '1';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch,CURLOPT_TIMEOUT,100);
        $chResponse = curl_exec($ch);
        $decoded = json_decode($chResponse, true);
        $decoded['MD5'] = array();
        $decoded['MD5'][] = md5_file($file['tmp_name']);
        if (isset($decoded['imgData'])) {
            foreach ($decoded['imgData'] as $size) {
                $decoded['MD5'][] = $size['md5'];
                $decoded['MD5'][] = $size['md5p'];
            }
        }
        return $decoded;
        // return JSON here. Make sure to use return!!!!
    })->then(function ($output) use (&$outputJSONs, $file) {

        $outputJSONs[] = $output;

    })->catch(function (Throwable $exception) use (&$outputJSONs) {
        // Error handle
        $outputJSONs[] = array('error' => 'total');
    });
}
// Run pool 
$pool->wait();

// Parse JSON, check for errors if a JSON has the key "error" set to "total" consider the whole thing a bust.
$failed = false;
foreach($outputJSONs as $image) {
    if ((isset($image['error']) && $image['error'] == 'total') || !isset($image['status'])) {
        $failed = true;
    }
}

if ($failed == true) {
    $array = array('code' => 'ERR_IMAGE_CONVERSION_FAILURE', 'message' => L::error_image_convert_failure);
    echo json_encode($array);
    exit();
}
$database = Postgres::getInstance();
$imageIDs = array();
$i = 0;

// Art theft checking
$stolen = array();
foreach ($outputJSONs as $json) {
    if (isset($json['imgData'])) {
        $returnVal = WFUtils::doArtTheftCheck($json['MD5']);
        if ($returnVal != false) {
            $stolen[] = $returnVal;
        }
    }
}

$confirmedStolen = array();
/**If stolen isn't empty, there's stuff posted before. Cal up the post and check, in order:
  *  - is the post deleted?
  *  - is the owner of the blog the current poster?
  *  
  * If either return yes, ignore it and continue for that image. 
  */
foreach ($stolen as $postID) {
    $post = new Post(intval($postID));
    if (!$post->failed && $post->postStatus != 'deleted' && $post->onBlog != $blog->ID) {
        $confirmedStolen[] = $postID;
    }
}

if (sizeof($confirmedStolen) != 0) {
    // Reblog everything in the stolen list and fail
    foreach ($confirmedStolen as $reblogThis) {
        $post = new Reblog();
        $reblogObj = new Post($reblogThis);
        if ($reblogObj->failed == false) {
            $post->createNew('', '', $blog->ID, $reblogObj->sourcePost, $reblogObj->postType, $reblogObj->ID);
        }
    }

    $data['code'] = 'ERR_STOLEN';
    $data['message'] = L::error_art_stolen;
    echo json_encode($data);
    exit();
}
// If it's not marked as stolen it doesn't matter and we can just insert them again, even into the art stuff

foreach ($outputJSONs as $json) {
    if (isset($json['imgData'])) {
        $data = $json['imgData'];

        $onServer = array($json['onServer']);
        $values = array(json_encode($data), true, $database->php_to_postgres($onServer), $_POST['caption'][$i], $_POST['description'][$i]);
        $imageID = $database->db_insert("INSERT INTO images (paths, is_art, servers, caption, accessibility_caption, version) VALUES ($1,$2,$3,$4,$5,2)", $values);
        if ($imageID != false) {
            $imageIDs[] = $imageID;
        } else {
            $failed = true;
        }
    }
    $i = $i + 1;
}

// MD5s can be batch checked. If there's a match, reblog instead. If there's multiple, reblog them all. 

if ($failed == true) {
    $array = array('code' => 'ERR_IMAGE_INSERT_FAILURE', 'message' => L::error_image_insert_failure);
    echo json_encode($array);
    exit();
}

// Handle post stuff

$data = array();

$additions = array();
if (isset($_POST['pollQuestion']) && trim($_POST['pollQuestion']) != '' && trim($_POST['pollQuestion']) != null) {
    $additions['poll'] = true;
    $additions['pollQuestion'] = $_POST['pollQuestion'];
    $additions['pollOptions'] = $_POST['pollOptions']; // Should be an array
    $additions['pollDeadline'] = $_POST['pollDeadline'];
    if (isset($_POST['multipleChoice']) && $_POST['multipleChoice'] == 'true') {
        $additions['pollVoteType'] = 'multiple';
    } else {
        $additions['pollVoteType'] = 'single';
    }
} else {
    $additions['poll'] = false;
}


    if ($_POST['submitType'] == 'post') {
        $type = 'post';
    } elseif ($_POST['submitType'] == 'draft') {
        $type = 'draft';
    } elseif ($_POST['submitType'] == 'queue') {
        if ($blog->settings['queueTag'] != null && $blog->settings['queueTag'] != '') {
            $_POST['postTags'] = $_POST['postTags'].', '.$blog->settings['queueTag'];
        }
        $type = 'queue';
    } elseif ($_POST['submitType'] == 'private') {
        $type = 'private';
    } else {
        $type = 'posted';
    }
    $post = new ArtPost();
    if ($post->createNew($_POST['postText'], substr($_POST['postTitle'],0,255), $_POST['postTags'], $blog->ID, $additions, $type, $imageIDs)) {
        // We need to insert the art data now we have the post ID
        foreach($outputJSONs as $key => $json) {
            $values = array($post->ID, $post->onBlog, $database->php_to_postgres($json['MD5']), $imageIDs[$key]);
            $database->db_insert("INSERT INTO art_data (post_id, on_blog, image_md5, image_id) VALUES ($1, $2, $3, $4)", $values);
        }
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_success;
    } else {
        $data['code'] = 'ERR_MISC_FAILURE';
        $data['message'] = L::error_unknown;
    }




echo json_encode($data);
