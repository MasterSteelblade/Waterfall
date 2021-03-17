<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

$data = array();
if ($session != false) {
    $messageObj = new Message($_POST['messageID']);
    if ($messageObj->failed) {
        $data['code'] = 'ERROR_NOT_FOUND';
        $data['message'] = L::error_message_not_found;
        echo json_encode($data);
        exit();
    }
    $onBlog = $messageObj->recipient;
    $blogObj = new Blog($onBlog);
    if ($blogObj->failed) {
        $data['code'] = 'ERROR_BLOG_NOT_FOUND';
        $data['message'] = L::error_blog_not_found;
        echo json_encode($data);
        exit();
    }
    $userID = $sessionObj->user->ID;
    if ($blogObj->ownerID == $userID || $blogObj->checkMemberPermission($userID, 'delete_asks')) {
        if ($messageObj->inboxDelete()) {
            $data['code'] = 'SUCCESS';
            $data['message'] = L::string_success;
        } else {
            $data['code'] = 'ERROR_DELETE_FAILED';
            $data['message'] = L::error_failed_to_delete;
        }
    } else {
        $data['code'] = 'ERROR_PERMISSIONS';
        $data['message'] = L::error_invalid_permissions;
    }
}
echo json_encode($data);
