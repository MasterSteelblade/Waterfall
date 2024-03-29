<?php 

require_once(__DIR__.'/../../includes/session.php');

// if no session, just die silently
if ($session == false) {
    exit();
}

// we're returning JSON
header('Content-Type: application/json');
$data = array();

// verify CSRF
$easyCSRF = new \EasyCSRF\EasyCSRF($sessionObj);
try {
    $easyCSRF->check($sessionObj->sessionData['csrfName'], $_POST['token'], 60*15, true);
} catch(\EasyCSRF\Exceptions\InvalidCsrfTokenException $e) {
    // csrf error! exit early
    $data['code'] = 'ERR_CSRF_FAILURE';
    $data['message'] = L::error_csrf;
    echo json_encode($data);
    exit();
}

$user = $sessionObj->user;
if (!$user) {
    // user is not logged in?
    $data['code'] = 'ERR_NOT_LOGGED_IN';
    $data['message'] = L::error_no_session;
    echo json_encode($data);
    exit();
}

// destroy session
if ($sessionObj->destroySession()) {
    $data['code'] = 'SUCCESS';    
    $data['message'] = L::string_success;
} else {
    $data['code'] = 'ERR_GENERIC_FAILURE';
    $data['message'] = L::error_unknown;
}

echo json_encode($data);