<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

$data = array();

$blog = new Blog($sessionObj->sessionData['activeBlog']);
if ($session == false) {
    $data['code'] = 'NO_SESSION';
    $data['message'] = "No session found. Try logging in again.";
    echo json_encode($data);
    exit();
}

if (!$blog->failed) {
    $return = $blog->createInvite($_POST['invRef']);
    if ($return == false) {
        $data['code']= 'ERR_FAILED';
        $data['code'] = "Failed to create invite...";
        echo json_encode($data);
    }
    $data['inviteURL'] = 'https://'.$_ENV['SITE_URL'].'/inv/'.$return;
    $data['code']= 'SUCCESS';
    $data['message'] = "Invite Created! Gives your friends the link below. ";
    echo json_encode($data);
} else {
    $data['code']= 'ERR_NO_BLOG';
    $data['message'] = "Failed";
    echo json_encode($data);
}