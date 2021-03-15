<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

$blog = new Blog();
$blog->getByBlogName($_POST['blogID']);
if ($session == false) {
    $data['code'] = 'NO_SESSION';
    $data['message'] = "You're not logged in.";
    echo json_encode($data);
    exit();
}

if (!$blog->failed) {
    if ($blog->ownerID == $sessionObj->user->ID) {
        if ($blog->ID == $sessionObj->user->mainBlog) {
            $data['code'] = 'ERR_MAIN_BLOG';
            $data['message'] = "You can't delete your main blog!";
            echo json_encode($data);
            exit();
        }
        $blog->deleteBlog();
        $sessionObj->sessionData['activeBlog'] = $sessionObj->user->mainBlog;
        $sessionObj->updateSession();
        $data['code'] = 'SUCCESS';
        $data['message'] = "Success!";
        echo json_encode($data);
    } else {
        $data['code'] = 'ERR_PERMISSIONS';
        $data['message'] = "You don't have permission to do that.";
        echo json_encode($data);
    }
}