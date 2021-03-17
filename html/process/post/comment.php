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
    $data['message'] = L::error_invalid_permissions;
    echo json_encode($data);
    exit();
}

$post = new Post($_POST['postID']);
if ($post->failed == true) {
    $data['code'] = 'ERR_POST_NOT_FOUND';
    $data['message'] = L::error_post_not_found;
    echo json_encode($data);
    exit();
}

if ($post->checkDNRStatus() == 'dni') {
    $data['code'] = 'ERR_DNI';
    $data['message'] = L::error_post_is_dni;
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
        $data['message'] = L::string_success;
    } else {
        $data['code'] = 'ERR_BACKEND_FAILURE';
        $data['message'] = L::error_unknown;
    }

} else {
    $data['code'] = 'ERR_EMPTY_TEXT';
    $data['message'] = L::error_no_content;
}

echo json_encode($data);