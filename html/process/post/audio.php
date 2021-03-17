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
if (isset($_FILES['albumArt'])) {
    $albumArt = $_FILES['albumArt'];
} else {
    $albumArt = false;
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


if (isset($_POST['audioType']) && $_POST['audioType'] == 'upload') {
    if (isset($_FILES['audioFile'])) {
        $audioFile = $_FILES['audioFile'];
    } else {
        $data['code'] = 'ERR_BAD_FILE';
        $data['message'] = L::error_no_audio;
        echo json_encode($data);
        exit();
    }

    $data = array();
    $database = Postgres::getInstance();

        if ($albumArt != false) {
            // Album Art 
            $randStr = WFUtils::generateRandomString(6);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $server = WFUtils::pickServer();
            $url = $server.'/image/add';
            curl_setopt($ch, CURLOPT_URL, $url);
            $postData = array();
            $postData['images'] = new CurlFile($albumArt['tmp_name']);
            $postData['isAudio'] = '1';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch,CURLOPT_TIMEOUT,100);
            $chResponse = curl_exec($ch);
            $json = json_decode($chResponse, true);
            if (isset($json['imgData'])) {
                $data = $json['imgData'];
                $onServer = array($json['onServer']);
                $values = array(json_encode($data), false, $database->php_to_postgres($onServer));
                $result = $database->db_insert("INSERT INTO images (paths, is_art, servers, version) VALUES ($1,$2,$3,2)", $values);
                if ($result != false) {
                    $imageID = $result;
                } else {
                    $imageID = 0;
                }
            }
        } else {
            $imageID = 0;
        }


    // If audio file is valid
    $values = array($_POST['artist'], $_POST['trackName'],  $imageID);
    $audioID = $database->db_insert("INSERT INTO audio (artist, title, album_art) VALUES ($1,$2,$3)", $values);

    $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $server = WFUtils::pickServer();
            $url = $server.'/audio/add';
            curl_setopt($ch, CURLOPT_URL, $url);
            $postData = array();
            $postData['audio'] = new CurlFile($audioFile['tmp_name']);
            $postData['audioID'] = $audioID;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch,CURLOPT_TIMEOUT,1000);
            $chResponse = curl_exec($ch);
            $json = json_decode($chResponse, true);
            unset($data);
            $data = array();
            if ($json['status'] != 'success') {
                // It failed, exit
                $data['code'] = 'ERR_NOT_AUDIO';
                echo json_encode($data);
                exit();
            }
            $duration = gmdate("i:s", $json['duration']);
            $duration = explode(':', $duration);
            $values = array($duration[0], $duration[1], $audioID);
            $database->db_update("UPDATE audio SET duration_minutes = $1, duration_seconds = $2 WHERE id = $3", $values);



    
        $post = new AudioPost();
        if ($post->createNew($_POST['postText'], substr($_POST['postTitle'],0,255), $_POST['postTags'], $blog->ID, $additions, $type, $audioID)) {
            $data['code'] = 'SUCCESS';
            $data['message'] = L::string_success;
        } else {
            $data['code'] = 'ERR_MISC_FAILURE';
            $data['message'] = L::error_unknown;
        }
        echo json_encode($data);
    } else {
        $embed = 'SPOTIFY:'.$_POST['embedID'];
        $post = new AudioPost();
        if ($post->createNew($_POST['postText'], substr($_POST['postTitle'],0,255), $_POST['postTags'], $blog->ID, $additions, $type, $embed, true)) {
            $data['code'] = 'SUCCESS';
            $data['message'] = L::string_success;
        } else {
            $data['code'] = 'ERR_MISC_FAILURE';
            $data['message'] = L::error_unknown;
        }
        echo json_encode($data);
    }
