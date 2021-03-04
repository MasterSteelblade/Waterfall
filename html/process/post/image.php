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
    echo json_encode($data);
    exit();
}


$blog = new Blog();
$blog->getByBlogName($_POST['onBlog']);
if ($blog->failed || ($blog->ownerID != $sessionObj->user->ID && !$blog->checkMemberPermission($sessionObj->user->ID, 'write_post'))) {
    $data['code'] = 'ERR_NOT_YOUR_BLOG';
    echo json_encode($data);
    exit();
}

use Spatie\Async\Pool;

$pool = Pool::create();

// Do files here. 
$files = reArrayFiles($_FILES['image']);

if (count($files) == 0) {
    $data['code'] = 'ERR_NO_IMAGES';
    echo json_encode($data);
    exit();
}
// Files are now organised in a somewhat sane manner. 
$outputJSONs = array();
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch,CURLOPT_TIMEOUT,100);
        $chResponse = curl_exec($ch);
        $decoded = json_decode($chResponse, true);
        return $decoded;
        // return JSON here. Make sure to use return!!!!
    })->then(function ($output) use (&$outputJSONs) {
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
    $array = array('code' => 'ERR_IMAGE_CONVERSION_FAILURE');
    echo json_encode($array);
    exit();
}
$database = Postgres::getInstance();
$imageIDs = array();
$i = 0;
foreach ($outputJSONs as $json) {
    if (isset($json['imgData'])) {
        $data = $json['imgData'];
        $onServer = array($json['onServer']);
        $values = array(json_encode($data), false, $database->php_to_postgres($onServer), $_POST['caption'][$i], $_POST['description'][$i]);
        $imageID = $database->db_insert("INSERT INTO images (paths, is_art, servers, caption, accessibility_caption, version) VALUES ($1,$2,$3,$4,$5, 2)", $values);
        if ($imageID != false) {
            $imageIDs[] = $imageID;
        } else {
            $failed = true;
        }
    }
    $i = $i + 1;
}

if ($failed == true) {
    $array = array('code' => 'ERR_IMAGE_INSERT_FAILURE');
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
    $post = new ImagePost();
    if ($post->createNew($_POST['postText'], substr($_POST['postTitle'],0,255), $_POST['postTags'], $blog->ID, $additions, $type, $imageIDs)) {
        $data['code'] = 'SUCCESS';
    } else {
        $data['code'] = 'ERR_MISC_FAILURE';
    }




echo json_encode($data);
