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
    $json = json_encode($data);
    echo $json;
} else {
    try {
        $easyCSRF->check($sessionObj->sessionData['csrfName'], $_POST['tokeItUp'], 60*15, true);
    } catch(InvalidCsrfTokenException $e) {
        $data['code'] = 'ERR_CSRF_FAILURE';
        echo json_encode($data);
        exit();
    }
    $redis = new WFRedis('login_failures');

    $failures = $redis->get($_SERVER['REMOTE_ADDR']);
    if ($failures >= 5) {
        $data['code'] = 'ERR_LOGIN_BAN';
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
            $redis->increment($_SERVER['REMOTE_ADDR']);
            $redis->expireIn($_SERVER['REMOTE_ADDR'], 600);
            $json = json_encode($data);
            echo $json;
            exit();
        } 
        if ($user->hasTwoFactor() && (!isset($twoFactor) || $twoFactor == '')) {
            // 2FA is there, but not in the form. 
            $data['code'] = 'ERR_2FA_NEEDED';
        } else {
            if ($user->hasTwoFactor() && isset($twoFactor)) {
                // Check the 2FA code. 
                if (!$user->verifyTwoFactor($twoFactor)) {
                    $data['code'] = 'ERR_INVALID_2FA';
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
                $redis->increment($_SERVER['REMOTE_ADDR']);
                $redis->expireIn($_SERVER['REMOTE_ADDR'], 600);

            } elseif ($sessionID === 0) {
                $data['code'] = 'ERR_BACKEND_FAILURE';
            } else {
                $data['code'] = 'SUCCESSFUL_LOGIN';
            }
        }

    } else {
        // That user doesn't exist. 
        $data['code'] = 'ERR_USER_NOT_FOUND';
    }

}
echo json_encode($data);
