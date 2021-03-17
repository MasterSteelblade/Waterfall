<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $blog = new Blog();
    if ($blog->failed) {
        $data['code'] = 'ERR_FAILED';
        $data['message'] = L::error_blog_not_found;
        echo json_encode($data);
        exit();
    }
    if ($blog->ownerID == $user->ID) {
        if ($blog->deleteBlog()) {
            $data['code'] = 'SUCCESS';
            $data['message'] = L::string_success;
            echo json_encode($data);
        } else {
            $data['code'] = 'ERR_FAILED';
            $data['message'] = L::error_unknown;
            echo json_encode($data);
        }
    } else {
        $data['code'] = 'ERR_NOT_YOUR_BLOG';
        $data['message'] = L::error_invalid_permissions;
        echo json_encode($data); 
    }
}
