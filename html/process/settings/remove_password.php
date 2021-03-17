<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $permittedBlog = new Blog();
    $permittedBlog->getByBlogName($_POST['removeBlog']);
    if ($permittedBlog->failed) {
        $data['blog'] = $_POST['editingblog'];
        $data['code'] = 'ERR_NOT_MEMBER';
        $data['message'] = L::error_not_member;
        echo json_encode($data);
        exit();
    } elseif ($permittedBlog->checkMemberPermission($sessionObj->user->ID, 'blog_settings') == false && $permittedBlog->ownerID != $sessionObj->user->ID) {
        $data['code'] = 'ERR_PERMISSIONS';
        $data['message'] = L::error_invalid_permissions;
        echo json_encode($data);
        exit();
    }

    if ($permittedBlog->resetPassword()) {
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_success;
        echo json_encode($data);
    } else {
        $data['code'] = 'ERR_GENERIC_FAILURE';
        $data['message'] = L::error_unknown;
        echo json_encode($data);
    }



}