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
if (!isset($_POST['editing']) || $_POST['editing'] == 0 || !is_numeric($_POST['editing'])) {
    $data['code'] = 'ERR_INVALID';
    $data['message'] = L::error_post_not_found;
    echo json_encode($data);
    exit();
}
$editing = new Post(intval($_POST['editing']));
if ($editing->failed) {
    $data['code'] = 'ERR_INVALID';
    $data['message'] = L::error_post_not_found;
    echo json_encode($data);
    exit();
}

$blog = new Blog($editing->onBlog);
if ($blog->failed || ($blog->ownerID != $sessionObj->user->ID && !$blog->checkMemberPermission($sessionObj->user->ID, 'edit_post'))) {
    $data['code'] = 'ERR_NOT_YOUR_BLOG';
    $data['message'] = L::error_invalid_permissions;
    echo json_encode($data);
    exit();
}

if (isset($_POST['pinned']) && $_POST['pinned'] == 'pinMe') {
    if ($editing->onBlog == $blog->ID) {
        $blog->updatePinnedPost($editing->ID);
    } else if ($blog->pinnedPost == $editing->ID) {
        $blog->clearPinnedPost();
    }
}
use Spatie\Async\Pool;

$data = array();

    if ($editing->postType == 'art' || $editing->postType == 'image') {

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
            $array = array('code' => 'ERR_IMAGE_CONVERSION_FAILURE', 'message' => L::error_image_convert_failure);
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
            $array = array('code' => 'ERR_IMAGE_INSERT_FAILURE', 'message' => L::error_image_insert_failure);
            echo json_encode($array);
            exit();
        }
        $editing->imageIDs = $imageIDs;
    }

    if (isset($_POST['postTitle'])) {
        $title = substr($_POST['postTitle'],0,255);
    } else {
        $title = '';
    }
    if (!$editing->isReblog) {
        if (isset($_POST['postTitle'])) {
            $editing->postTitle = $_POST['postTitle'];
        } else {
            $editing->postTitle = '';
        }
    }
    if ($_POST['submitType'] == 'post') {
        if ($editing->postStatus != 'private') {
            $editing->postStatus = 'posted';
        }
        $timestamp = new DateTime();
        if ($editing->postStatus == 'drafted') {
            $editing->timestamp = $timestamp->format("Y-m-d H:i:s.u");
            $undrafting = true;
        }
    } elseif ($_POST['submitType'] == 'draft') {
        $editing->postStatus = 'draft';

    }
    $editing->createTags($_POST['postTags']);
    $editing->content = $_POST['postText'];
    if ($editing->updatePost()) {
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_success;
    } else {
        $data['code'] = 'ERR_MISC_FAILURE';
        $data['message'] = L::error_unknown;
    }


echo json_encode($data);