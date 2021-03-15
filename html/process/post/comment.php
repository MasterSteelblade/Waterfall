<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

$data = array();

if ($session == false) {
    $data['code'] = 'NO_SESSION';
    echo json_encode($data);
    exit();
}


$blog = new Blog($sessionObj->sessionData['activeBlog']);
if ($blog->failed || ($blog->ownerID != $sessionObj->user->ID && !$blog->checkMemberPermission($sessionObj->user->ID, 'write_post'))) {
    $data['code'] = 'ERR_NOT_YOUR_BLOG';
    $data['message'] = "Not your blog, or you don't have permission to answer this.";
    echo json_encode($data);
    exit();
}

$post = new Post($_POST['postID']);
if ($post->failed == true) {
    $data['code'] = 'ERR_POST_NOT_FOUND';
    $data['message'] = "Post not found";
    echo json_encode($data);
    exit();
}

if ($post->checkDNRStatus() == 'dni') {
    $data['code'] = 'ERR_DNI';
    $data['message'] = "The poster has marked this as DNI, and you can't leave a comment.";
    echo json_encode($data);
    exit();
}

if (isset($_POST['text']) && WFUtils::textContentCheck($_POST['text'])) {
    $note = new Note();
    $note->noteType = 'comment';
    $note->noteRecipient = $post->onBlog;
    $note->noteSender = $blog->ID;
    $note->postID = $_POST['postID'];
    $note->sourcePost = $post->sourcePost;
    $note->comment = WFText::makeTextSafe($_POST['text']);
    if ($note->createNote() != false) {
        $data['code'] = 'SUCCESS';
        $data['message'] = "Success";
    } else {
        $data['code'] = 'ERR_BACKEND_FAILURE';
        $data['message'] = "Unknown backend failure";
    }

} else {
    $data['code'] = 'ERR_EMPTY_TEXT';
    $data['message'] = "No text detected!";
}

echo json_encode($data);