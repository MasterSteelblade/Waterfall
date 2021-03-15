<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $blog = new Blog();
    $blog->getByBlogName($_POST['leavingBlog']);
    if (!$blog->failed) {
        if ($blog->ID == $sessionObj->sessionData['activeblog']) {
            $data['code'] = 'ERR_ACTIVE_BLOG';
            $data['message'] = "Can't leave your active blog! Switch to another first.";
            echo json_encode($data);
            exit();
        }
        if ($blog->checkMemberPermission($sessionObj->user->ID, 'is_member') == true && $blog->ownerID != $sessionObj->user->ID) {
            $obj = $blog->getMemberPermissionObject($sessionObj->user->ID);
            if ($obj->removeMember()) {
                $data['code'] = 'SUCCESS';
                $data['message'] = "Success!";
                echo json_encode($data);

                exit();
            } else {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                $data['message'] = "Unknown failure";
                echo json_encode($data);
                exit();
            }
        } else {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = "You don't have permission to do that.";
            echo json_encode($data);
        }
    } else {
        $data['code'] = 'ERR_NOT_FOUND';
        $data['message'] = "Blog not found";
        echo json_encode($data);
    }
}