<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $blog = new Blog();
    $blog->getByBlogName($_POST['blockBlog']);
    if (!$blog->failed) {
        $userID = $blog->ownerID;
            if ($user->block($userID)) {
                $data['code'] = 'SUCCESS';
                $data['message'] = "This user is now blocked.";
                echo json_encode($data);
                exit();
            } else {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                $data['message'] = "Generic backend failure";
                echo json_encode($data);
                exit();
            }
    } else {
        $data['code'] = 'ERR_NOT_FOUND';
        echo json_encode($data);
    }
}