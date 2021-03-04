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
        echo json_encode($data);
        exit();
    } elseif ($permittedBlog->checkMemberPermission($sessionObj->user->ID, 'blog_settings') == false && $permittedBlog->ownerID != $sessionObj->user->ID) {
        $data['code'] = 'ERR_PERMISSIONS';
        echo json_encode($data);
        exit();
    }

    if ($permittedBlog->resetPassword()) {
        $data['code'] = 'SUCCESS';
        echo json_encode($data);
    } else {
        $data['code'] = 'ERR_GENERIC_FAILURE';
        echo json_encode($data);
    }



}