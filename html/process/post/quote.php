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

if (isset($_POST['quote']) && WFUtils::textContentCheck($_POST['quote']) && isset($_POST['attribution']) && WFUtils::textContentCheck($_POST['attribution'])) {
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
    $post = new QuotePost();
    if ($post->createNew($_POST['postText'], $_POST['quote'], $_POST['postTags'], $blog->ID, $additions, $type, $_POST['attribution'])) {
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