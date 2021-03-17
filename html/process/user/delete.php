<?php 
require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');
ob_start();
use EasyCSRF\Exceptions\InvalidCsrfTokenException;
// Force this to be a JSON return for a laugh
$easyCSRF = new EasyCSRF\EasyCSRF($sessionObj);
header('Content-type: application/json');

$data = array();
if ($session === false || !isset($_POST['deletePassword'])) {
    $data['code'] = 'ERR_GENERIC_FAILURE';
    $data['message'] = L::error_unknown;
    echo json_encode($data);
    $size = ob_get_length();
    header("Content-Encoding: none");
    header("Content-Length: {$size}");
    header("Connection: close");
    ob_end_flush();
    ob_flush();
    exit();
} else {
    try {
        $easyCSRF->check($sessionObj->sessionData['csrfName'], $_POST['tokeItUp'], 60*15, true);
    } catch(InvalidCsrfTokenException $e) {
        $data['code'] = 'ERR_CSRF_FAILURE';
        $data['message'] = L::error_csrf;
        echo json_encode($data);
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        ob_end_flush();
        ob_flush();
        flush();
        exit();
    }
    $user = $sessionObj->user;
    if (password_verify($_POST['deletePassword'], $user->password)) {
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_goodbye;
        $sessionObj->destroySession();
        echo json_encode($data);
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        ob_end_flush();
        ob_flush();
        flush();
        if (is_callable('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        $user->deleteAccount();
    } else {
        // Wrong password. 
        $data['code'] = 'ERR_WRONG_PASS';
        $data['message'] = L::error_invalid_credentials;
        echo json_encode($data);
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        ob_end_flush();
        ob_flush();
        exit();
    }
}

