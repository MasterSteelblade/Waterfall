<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');


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


if (isset($_POST['videoType']) && $_POST['videoType'] == 'upload') {
    if (isset($_FILES['videoFile'])) {
        $videoFile = $_FILES['videoFile'];
    } else {
        $data['code'] = 'ERR_BAD_FILE';
        $data['message'] = L::error_no_video;
        echo json_encode($data);
        exit();
    }

    $data = array();
    $database = Postgres::getInstance();

    // If video file is valid
    $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $values = array('{}', '{}', 'waiting');
            $res = $database->db_insert("INSERT INTO video (paths, servers, transcode_status) VALUES ($1, $2, $3)", $values);
            if ($res) {
                $videoID = $res;
            } else {
                $data['code'] = 'ERR_MISC_FAILURE';
                $data['message'] = L::error_unknown;
                echo json_encode($data);
                exit();
            }
            $server = WFUtils::pickServer();
            $url = $server.'/video/add';
            curl_setopt($ch, CURLOPT_URL, $url);
            $postData = array();
            $postData['video'] = new CurlFile($videoFile['tmp_name']);
            $postData['videoID'] = $videoID;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch,CURLOPT_TIMEOUT,1000);
            $chResponse = curl_exec($ch);
            $json = json_decode($chResponse, true);
            unset($data);
            $data = array();
            if ($json['status'] != 'success') {
                // It failed, exit
                $data['code'] = 'ERR_NOT_VIDEO';
                $data['message'] = L::error_no_video;
                echo json_encode($data);
                exit();
            }
            $data['path'] = $json['path'];
            $md5 = $json['md5'];
            $duration = gmdate("i:s", $json['duration']);
            $duration = explode(':', $duration);


    
        $post = new VideoPost();
        if ($post->createNew($_POST['postText'], substr($_POST['postTitle'],0,255), $_POST['postTags'], $blog->ID, $additions, $type, $videoID)) {
            $data['code'] = 'SUCCESS';
            $data['code'] = L::string_success;
        } else {
            $data['code'] = 'ERR_MISC_FAILURE';
            $data['message'] = L::error_unknown;

        }
        echo json_encode($data);
    } else {
        if ($_POST['embedType'] == 'youtube') {
            $embed = 'YOUTUBE:'.$_POST['embedID'];
        } else if ($_POST['embedType'] == 'vimeo') {
            $embed = 'VIMEO:'.$_POST['embedID'];
        } else {
            $embed = '';
        }
        $post = new VideoPost();
        if ($post->createNew($_POST['postText'], substr($_POST['postTitle'],0,255), $_POST['postTags'], $blog->ID, $additions, $type, $embed, true)) {
            $data['code'] = 'SUCCESS';
            $data['code'] = L::string_success;
        } else {
            $data['code'] = 'ERR_MISC_FAILURE';
            $data['message'] = L::error_unknown;

        }
        echo json_encode($data);
    }
