<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

$data = array();

$blog = new Blog($sessionObj->sessionData['activeBlog']);
if ($session == false) {
    $data['code'] = 'NO_SESSION';
    $data['message'] = L::error_no_session;
    echo json_encode($data);
    exit();
}

if (!$blog->failed) {
    $return = $blog->createInvite($_POST['invRef']);
    if ($return == false) {
        $data['code']= 'ERR_FAILED';
        $data['code'] = L::error_failed_invite_create;
        echo json_encode($data);
    }
    $data['inviteURL'] = 'https://'.$_ENV['SITE_URL'].'/inv/'.$return;
    $data['code']= 'SUCCESS';
    $data['message'] = L::blog_settings_invite_created;
    echo json_encode($data);
} else {
    $data['code']= 'ERR_NO_BLOG';
    $data['message'] = L::error_no_session; // Shouldn't get here, but it's the same error.
    echo json_encode($data);
}