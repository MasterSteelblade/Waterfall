<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

$blog = new Blog();
$blog->getByBlogName($_POST['blogID']);
if ($session == false) {
    $data['code'] = 'NO_SESSION';
    echo json_encode($data);
    exit();
}

if (!$blog->failed) {
    if ($blog->ownerID == $sessionObj->user->ID) {
        if ($blog->ID == $sessionObj->user->mainBlog) {
            $data['code'] = 'ERR_MAIN_BLOG';
            echo json_encode($data);
            exit();
        }
        $blog->deleteBlog();
        $sessionObj->sessionData['activeBlog'] = $sessionObj->user->mainBlog;
        $sessionObj->updateSession();
        $data['code'] = 'SUCCESS';
        echo json_encode($data);
    } else {
        $data['code'] = 'ERR_PERMISSIONS';
        echo json_encode($data);
    }
}