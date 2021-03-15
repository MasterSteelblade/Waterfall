<?php 

require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');

header('Content-type: application/json');


$data = array();
if ($session == false) {
    $data['code'] = 'ERR_GENERIC_FAILURE';
} else {
    if (isset($_POST['switchTo'])) {
        $blog = new Blog();
        $blog->getByBlogName($_POST['switchTo']);
        if (!$blog->failed) {
            if ($blog->ownerID == $sessionObj->sessionData['userID'] || $blog->checkMemberPermission($sessionObj->user->ID, 'is_member')) {
                $sessionObj->sessionData['activeBlog'] = $blog->ID;
                $sessionObj->updateSession();
                $data['code'] = 'SUCCESS';
                $data['message'] = "Switching blog...";
            } else {
                $data['code'] = 'ERR_NOT_YOUR_BLOG';
                $data['message'] = "You can't switch to a blog you don't own.";

            }
        } else {
            $data['code'] = 'ERR_BLOG_DOES_NOT_EXIST';
            $data['message'] = "This blog does not exist.";
        }
    } else {
        $data['code'] = 'ERR_NO_BLOG_SET';
        $data['message'] = "you didn't specify a blog to switch to";
    }
    
}
    echo json_encode($data);
    exit();