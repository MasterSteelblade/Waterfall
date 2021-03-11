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
    echo json_encode($data);
    exit();
}

$user = $sessionObj->user;
$secretKey = $sessionObj->sessionData['totpsecret'];
if ($secretKey == null || $secretKey == '') {
    // oh no! we don't have a secret key, so we can't really do anything
    $data['code'] = 'ERR_NO_SECRET';
    echo json_encode($data);
    exit();
}

// check the TOTP code, if it's valid enable 2FA
$result = $user->enableTwoFactor($secretKey, trim($_POST['totpcode']));
if ($result == false) {
    // invalid TOTP code or an error occurred, exit early
    $data['code'] = 'ERR_INVALID_2FA';
    echo json_encode($data);
    exit();
} else if ($result == 0) {
    // result == 0 is a special case - the database update failed
    $data['code'] = 'ERR_BACKEND_FAILURE';
    echo json_encode($data);
    exit();
}

// if we get here, 2FA is now enabled!
// get rid of the secret from the session
$sessionObj->sessionData['totpsecret'] = null;
$sessionObj->updateSession();

// and return success
$data['code'] = 'SUCCESS';
echo json_encode($data);