<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $nameCheck = WFUtils::blogNameCheck($_POST['createBlog']);
    if ($nameCheck == false) {
        $data['code'] = 'ERR_BLOG_EXISTS';
        $data['message'] = L::error_blog_url_taken;
        echo json_encode($data);
    } else {
        $blog = new Blog();
        $blog->ownerID = $user->ID;
        $blog->blogName = WFUtils::urlFixer($_POST['createBlog']);
        if ($blog->createBlog()) {
            $data['code'] = 'SUCCESS';
            $data['message'] = L::string_success;
            echo json_encode($data);
        } else {
            $data['code'] = 'ERR_FAILED';
            $data['message'] = L::error_unknown;
            echo json_encode($data);
        }
    }
}