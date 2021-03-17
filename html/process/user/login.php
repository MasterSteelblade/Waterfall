<?php 

require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');

header('Content-type: application/json');
use EasyCSRF\Exceptions\InvalidCsrfTokenException;
// Force this to be a JSON return for a laugh
$easyCSRF = new EasyCSRF\EasyCSRF($sessionObj);
$data = array();
if ($session !== false) {
    // The user is already logged in. 
    $data['code'] = 'ERR_ALREADY_LOGGED_IN';
    $data['message'] = L::error_already_logged_in;
    $json = json_encode($data);
    echo $json;
} else {
    try {
        $easyCSRF->check($sessionObj->sessionData['csrfName'], $_POST['tokeItUp'], 60*15, true);
    } catch(InvalidCsrfTokenException $e) {
        $data['code'] = 'ERR_CSRF_FAILURE';
        $data['message'] = L::error_csrf;
        echo json_encode($data);
        exit();
    }
    $redis = new WFRedis('login_failures');

    $failures = $redis->get($_SERVER['REMOTE_ADDR']);
    if ($failures >= 5) {
        $data['code'] = 'ERR_LOGIN_BAN';
        $data['message'] = L::error_login_ban;
        $json = json_encode($data);
        echo $json;
        exit();
    }
    $emailAddress = $_POST['emailAddress'];
    $password = $_POST['password'];
    if (isset($_POST['twoFactorCode'])) {
        // Note: It's possible this may also be an empty string. 
        // Check accordingly. 
        $twoFactor = $_POST['twoFactorCode'];
    }
    // First, retrieve the user by their email, if possible. 
    $user = new User();
    if ($user->getByEmail($emailAddress) !== false) {
        // Check if they have 2FA, and whether we have it set. 
        if (password_verify($password, $user->password) == false) {
            $data['code'] = 'ERR_INVALID_CREDS';
            // This uses a special error instead of the generic, since it slightly screws up
            // brute force attacks to try and figure out who has an account or not. 
            // It's considered good practice. 
            $data['message'] = L::error_login_invalid_credentials;
            $redis->increment($_SERVER['REMOTE_ADDR']);
            $redis->expireIn($_SERVER['REMOTE_ADDR'], 600);
            $json = json_encode($data);
            echo $json;
            exit();
        } 
        if ($user->hasTwoFactor() && (!isset($twoFactor) || $twoFactor == '')) {
            // 2FA is there, but not in the form. 
            $data['code'] = 'ERR_2FA_NEEDED';
            $data['message'] = L::error_two_fa_needed;
        } else {
            if ($user->hasTwoFactor() && isset($twoFactor)) {
                // Check the 2FA code. 
                if (!$user->verifyTwoFactor($twoFactor)) {
                    $data['code'] = 'ERR_INVALID_2FA';
                    $data['message'] = L::error_two_fa_invalid;
                    echo json_encode($data);
                    exit(); // Exit early to make things cleaner. 
                }
            }

            // If we get here, there's no 2FA, or it passed. 
            // Note: 0 is distinct from false for the login command and means
            // something different. 
            $sessionID = $user->login($emailAddress, $password);
            if ($sessionID === false) {
                $data['code'] = 'ERR_INVALID_CREDS';
                $data['message'] = L::error_login_invalid_credentials;

                $redis->increment($_SERVER['REMOTE_ADDR']);
                $redis->expireIn($_SERVER['REMOTE_ADDR'], 600);

            } elseif ($sessionID === 0) {
                $data['code'] = 'ERR_BACKEND_FAILURE';
                $data['message'] = L::error_unknown;
            } else {
                $data['code'] = 'SUCCESSFUL_LOGIN';
                $data['message'] = L::login_success;
            }
        }

    } else {
        // That user doesn't exist. I don't know why I separated this out to begin with, 
        // but it gets the same treatment as above.
        $data['code'] = 'ERR_INVALID_CREDS';
        $data['message'] = L::error_login_invalid_credentials;
    }

}
echo json_encode($data);
