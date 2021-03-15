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
        $data['message'] = "This user is not a member of this blog";
        echo json_encode($data);
        exit();
    } elseif ($permittedBlog->checkMemberPermission($sessionObj->user->ID, 'blog_settings') == false && $permittedBlog->ownerID != $sessionObj->user->ID) {
        $data['code'] = 'ERR_PERMISSIONS';
        $data['message'] = "You don't have permission to do that.";
        echo json_encode($data);
        exit();
    }

    if ($permittedBlog->resetPassword()) {
        $data['code'] = 'SUCCESS';
        $data['message'] = "Success!";
        echo json_encode($data);
    } else {
        $data['code'] = 'ERR_GENERIC_FAILURE';
        $data['message'] = "Unknown failure";
        echo json_encode($data);
    }



}