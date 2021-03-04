<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $blog = new Blog();
    if ($blog->failed) {
        $data['code'] = 'ERR_FAILED';
        echo json_encode($data);
        exit();
    }
    if ($blog->ownerID == $user->ID) {
        if ($blog->deleteBlog()) {
            $data['code'] = 'SUCCESS';
            echo json_encode($data);
        } else {
            $data['code'] = 'ERR_FAILED';
            echo json_encode($data);
        }
    } else {
        $data['code'] = 'ERR_NOT_YOUR_BLOG';
        echo json_encode($data); 
    }
}
