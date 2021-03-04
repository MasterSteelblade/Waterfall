<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

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

if (isset($_POST['postText']) && (WFUtils::textContentCheck($_POST['postText']) || $_POST['postTitle'] != '')) {
    if ($_POST['submitType'] == 'post') {
        $type = 'post';
    } elseif ($_POST['submitType'] == 'draft') {
        $type = 'draft';
    } elseif ($_POST['submitType'] == 'queue') {
        $type = 'queue';
    } elseif ($_POST['submitType'] == 'private') {
        $type = 'private';
    } else {
        $type = 'posted';
    }
    $post = new ChatPost();
    if ($post->createNew($_POST['postText'], $_POST['postTitle'], $_POST['postTags'], $blog->ID, $additions, $type)) {
        $data['code'] = 'SUCCESS';
    } else {
        $data['code'] = 'ERR_MISC_FAILURE';
    }
} else {
    $data['code'] = 'ERR_EMPTY_TEXT';
}

echo json_encode($data);