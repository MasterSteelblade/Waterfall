<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $blog = new Blog();
    $blog->getByBlogName($_POST['unblockBlog']);
    if (!$blog->failed) {
        $userID = $blog->ownerID;
            if ($user->unblock($userID)) {
                $data['code'] = 'SUCCESS';
                $data['message'] = "User unblocked!";
                echo json_encode($data);
                exit();
            } else {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                $data['message'] = "Unknown failure";
                echo json_encode($data);
                exit();
            }
    } else {
        $data['code'] = 'ERR_NOT_FOUND';
        $data['message'] = "Blog not found.";
        echo json_encode($data);
    }
}