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
    $data['message'] = L::error_invalid_permissions;
    echo json_encode($data);
    exit();
}

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

if (isset($_POST['url'])) {
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
    $post = new LinkPost();
    $linkData = array();
    $linkData['url'] = $_POST['url'];
    $linkData['image'] = $_POST['pageImage'];
    $linkData['description'] = $_POST['pageDescription'];
    $linkData['title'] = $_POST['pageTitle'];
    $linkJSON = json_encode($linkData);
    if ($post->createNew($_POST['postText'], substr($_POST['postTitle'],0,255), $_POST['postTags'], $blog->ID, $additions, $type, $linkJSON)) {
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_success;
    } else {
        $data['code'] = 'ERR_MISC_FAILURE';
        $data['message'] = L::error_unknown;
    }
} else {
    $data['code'] = 'ERR_EMPTY_TEXT';
    $data['message'] = L::error_no_content;
}

echo json_encode($data);