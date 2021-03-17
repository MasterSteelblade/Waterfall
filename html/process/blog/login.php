<?php 

require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');

header('Content-type: application/json');


$data = array();
if ($session == false) {
    $data['code'] = 'ERR_GENERIC_FAILURE';
    $data['message'] = L::error_generic_backend;
} else {
    $loginBlog = new Blog();
    $loginBlog->getByBlogName($_POST['blogName']);
    if ($loginBlog->failed) {
        $data['code'] = 'ERR_THIS_BLOG_NOT_FOUND';
        $data['message'] = L::error_private_blog_not_found;
        echo json_encode($data);
        exit();
    } 
    if (password_verify($_POST['password'], $loginBlog->password)) {
        // Success. 
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_success;
        $sessionObj->sessionData['blogLogins'][$loginBlog->ID] = (time() + 1800);
        $sessionObj->updateSession();
    } else {
        $data['code'] = 'ERR_PASSWORD_WRONG';
        $data['message'] = L::error_private_blog_wrong_password;
    }
}

echo json_encode($data);
exit();