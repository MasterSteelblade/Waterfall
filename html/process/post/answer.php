<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');


if ($session == false) {
    $data['code'] = 'NO_SESSION';
    $data['message'] = L::error_no_session;
    echo json_encode($data);
    exit();
}
$messageID = $_POST['answering'];
$message = new Message($messageID);
if ($message->failed) {
    $data['code'] = 'ERR_INVALID_MESSAGE';
    $data['message'] = L::error_message_not_found;
    echo json_encode($data);
    exit();
}


$blog = new Blog($message->recipient);

$data = array();


// Do permission checks here
if (!$blog->failed && ($blog->ownerID == $sessionObj->user->ID || $blog->checkMemberPermission($sessionObj->user->ID, 'answer_asks'))) {
    // Proceed
} else {
    $data['code'] = 'ERR_PERMISSION_FAILURE';
    $data['message'] = L::error_invalid_permissions;
    echo json_encode($data);
    exit();
}


if (isset($_POST['answerPrivately']) && $_POST['answerPrivately'] == 'true') {
    $privateAnswer = true;
} else {
    $privateAnswer = false;
}


if (isset($_POST['postText']) && WFUtils::textContentCheck($_POST['postText'])) {
    if ($_POST['submitType'] == 'post') {
        $type = 'post';
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
    $post = new AnswerPost();
    if ($post->createNew($_POST['postText'], $messageID, $_POST['postTags'], $blog->ID, $privateAnswer, $type)) {
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_posted;
    } else {
        $data['code'] = 'ERR_MISC_FAILURE';
        $data['message'] = L::error_unknown;
    }
} else {
    $data['code'] = 'ERR_EMPTY_TEXT';
    $data['message'] = L::error_no_content;
}

echo json_encode($data);