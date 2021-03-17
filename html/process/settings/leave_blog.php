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
            $data['message'] = L::error_leave_active;
            echo json_encode($data);
            exit();
        }
        if ($blog->checkMemberPermission($sessionObj->user->ID, 'is_member') == true && $blog->ownerID != $sessionObj->user->ID) {
            $obj = $blog->getMemberPermissionObject($sessionObj->user->ID);
            if ($obj->removeMember()) {
                $data['code'] = 'SUCCESS';
                $data['message'] = L::string_success;
                echo json_encode($data);

                exit();
            } else {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                $data['message'] = L::error_unknown;
                echo json_encode($data);
                exit();
            }
        } else {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = L::error_invalid_permissions;
            echo json_encode($data);
        }
    } else {
        $data['code'] = 'ERR_NOT_FOUND';
        $data['message'] = L::error_blog_not_found;
        echo json_encode($data);
    }
}