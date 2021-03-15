<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

$post = new Post($_POST['postID']);
if ($session == false) {
    $data['code'] = 'NO_SESSION';
    $data['message'] = "No session detected. Try logging in again.";
    echo json_encode($data);
    exit();
}

if (!$post->failed) {
    if ($post->onBlog == $sessionObj->sessionData['activeBlog']) {
        $post->deletePost();
        $data['code'] = 'SUCCESS';
        $data['message'] = "Success!";
        echo json_encode($data);
    } else {
        $data['code'] = 'ERR_NOT_YOU_POST';
        $data['message'] = "This isn't your post.";
        echo json_encode($data);
    }
}