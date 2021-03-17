<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');


if ($session == false) {
    $data['code'] = 'NO_SESSION';
    $data['message'] = L::error_no_session;
    echo json_encode($data);
    exit();
}
if (!isset($_POST['reblogging']) || $_POST['reblogging'] == 0 || !is_numeric($_POST['reblogging'])) {
    $data['code'] = 'ERR_INVALID';
    $data['message'] = L::error_post_not_found;
    echo json_encode($data);
    exit();
}
$reblogging = new Post(intval($_POST['reblogging']));
if ($reblogging->failed) {
    $data['code'] = 'ERR_INVALID';
    $data['message'] = L::error_post_not_found;
    echo json_encode($data);
    exit();
}
$sourcePost = $reblogging->sourcePost;
$blog = new Blog();
$blog->getByBlogName($_POST['onBlog']);
if ($blog->failed || ($blog->ownerID != $sessionObj->user->ID && !$blog->checkMemberPermission($sessionObj->user->ID, 'write_post'))) {
    $data['code'] = 'ERR_NOT_YOUR_BLOG';
    $data['message'] = L::error_invalid_permissions;
    echo json_encode($data);
    exit();
}

$data = array();

    if ($_POST['submitType'] == 'post') {
        $type = 'post';
    } elseif ($_POST['submitType'] == 'draft') {
        $type = 'draft';
    } elseif ($_POST['submitType'] == 'queue') {
        if ($blog->settings['queueTag'] != null && $blog->settings['queueTag'] != '') {
            $_POST['postTags'] = $_POST['postTags'].', '.$blog->settings['queueTag'];
        }
        $type = 'queue';
    } else {
        $type = 'posted';
    }
    $post = new Reblog();
    if ($post->createNew($_POST['postText'], $_POST['postTags'], $blog->ID, $sourcePost, $type, $reblogging->ID)) {
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_success;
    } else {
        $data['code'] = 'ERR_MISC_FAILURE';
        $data['message'] = L::error_unknown;
    }


echo json_encode($data);