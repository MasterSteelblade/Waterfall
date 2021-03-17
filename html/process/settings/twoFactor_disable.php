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

// check the user's password
if (!$user->confirmPassword($_POST['password'])) {
    // nu-uh! exit early
    $data['code'] = 'ERR_INVALID_CREDS';
    $data['message'] = L::error_invalid_credentials;
    echo json_encode($data);
    exit();
}

// if we get here, password is correct, we can go ahead and disable 2FA
if ($user->disableTwoFactor()) {
    $data['code'] = 'SUCCESS';
    $data['message'] = L::two_factor_disabled_success;
} else {
    $data['code'] = 'ERR_BACKEND_FAILURE';
    $data['message'] = L::error_unknown;
}

echo json_encode($data);