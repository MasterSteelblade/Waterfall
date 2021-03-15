<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

$data = array();
if ($session != false) {
    $messageObj = new Message($_POST['messageID']);
    if ($messageObj->failed) {
        $data['code'] = 'ERROR_NOT_FOUND';
        $data['message'] = "Message not found.";
        echo json_encode($data);
        exit();
    }
    $onBlog = $messageObj->sender;
    $blogObj = new Blog($onBlog);
    if ($blogObj->failed) {
        $data['code'] = 'ERROR_BLOG_NOT_FOUND';
        $data['message'] = "Blog not found.";
        echo json_encode($data);
        exit();
    }
    $userID = $sessionObj->user->ID;
    if ($blogObj->ownerID == $userID || $blogObj->checkMemberPermission($userID, 'delete_asks')) {
        if ($messageObj->outboxDelete()) {
            $data['code'] = 'SUCCESS';
            $data['message'] = "Success!";
        } else {
            $data['code'] = 'ERROR_DELETE_FAILED';
            $data['message'] = "Failed to delete.";

        }
    } else {
        $data['code'] = 'ERROR_PERMISSIONS';
        $data['message'] = "I can't let you do that, Dave.";
    }
}
echo json_encode($data);
