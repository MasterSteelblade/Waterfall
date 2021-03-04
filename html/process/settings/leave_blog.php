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
            echo json_encode($data);
            exit();
        }
        if ($blog->checkMemberPermission($sessionObj->user->ID, 'is_member') == true && $blog->ownerID != $sessionObj->user->ID) {
            $obj = $blog->getMemberPermissionObject($sessionObj->user->ID);
            if ($obj->removeMember()) {
                $data['code'] = 'SUCCESS';
                echo json_encode($data);
                exit();
            } else {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                echo json_encode($data);
                exit();
            }
        } else {
            $data['code'] = 'ERR_PERMISSIONS';
            echo json_encode($data);
        }
    } else {
        $data['code'] = 'ERR_NOT_FOUND';
        echo json_encode($data);
    }
}