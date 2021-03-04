<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

$post = new Post($_POST['postID']);
if ($session == false) {
    $data['code'] = 'NO_SESSION';
    echo json_encode($data);
    exit();
}

if (!$post->failed) {
    if ($post->hasBlogLiked($sessionObj->sessionData['activeBlog'])) {
        $post->unlikePost($sessionObj->sessionData['activeBlog']);
    } else {
        $post->likePost($sessionObj->sessionData['activeBlog']);
    }
}