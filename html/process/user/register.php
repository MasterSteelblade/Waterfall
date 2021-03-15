<?php 

require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');

header('Content-type: application/json');
// Force this to be a JSON return for a laugh
if (isset($_POST['g-recaptcha-response'])) {
    $captcha = $_POST['g-recaptcha-response'];
    $secretKey = $_ENV['CAPTCHA_SECRETKEY'];
    $captchaIP = $_SERVER['REMOTE_ADDR'];
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
    $response = file_get_contents($url);
    $responseKeys = json_decode($response,true); // Should return JSON with success as true
}
if (!$captcha || !$responseKeys['success']) {
    $data['code'] = 'ERR_WRONG_CAPTCHA';
    $data['message'] = "Wrong captcha!";
    echo json_encode($data);
    exit();
}
$data = array();
if ($session !== false) {
    $data['code'] = 'ERR_ALREADY_LOGGED_IN';
    $data['message'] = "You're already logged in!";
} else {
    try {
        $DOB = new DateTime($_POST['birthday']);
    } catch (\Throwable $th) {
        $data['code'] = 'ERR_INVALID_DATE';
        $data['message'] = "Invalid date format! Use YYYY-MM-DD.";
        echo json_encode($data);
        exit();
    }
    $now = new DateTime();
    $age = $now->diff($DOB);
    if ($age->y < 13) {
        // Too young :pensive:
        $data['code'] = 'ERR_TOO_YOUNG';
        $data['message'] = "Sorry, for legal reasons we can't allow under 13s to join.";
        echo json_encode($data);
        exit();
    }
    if (strlen($_POST['password']) < 6) {
        $data['code'] = 'ERR_PASSWORD_SHORT';
        $data['message'] = "Your password was too short, it needs to be at least six characters";
        echo json_encode($data);
        exit();
    }
    if (strlen($_POST['blogName']) < 3) {
        $data['code'] = 'ERR_BLOG_SHORT';
        $data['message'] = "Blog name too short. It needs to be at least three characters.";
        echo json_encode($data);
        exit();
    }
    if ($_POST['password'] != $_POST['confirmPassword']) {
        $data['code'] = 'ERR_PASSWORD_MISMATCH';
        $data['message'] = "Your passwords didn't match!";
        echo json_encode($data);
        exit();
    }
    if (WFUtils::blogNameCheck($_POST['blogName']) == false) {
        $data['code'] = 'ERR_BLOG_TAKEN';
        $data['code'] = "This blog name is taken.";
        echo json_encode($data);
        exit();
    }
    $userTemp = new User();
    if (WFUtils::emailCheck($_POST['emailAddress']) == false) {
        $data['code'] = 'ERR_USER_EXISTS';
        $data['message'] = "A user already exists with this email address.";
        echo json_encode($data);
        exit();
    }
    // If we get this far we're probably good lol
    $user = new User();
    if (!isset($_POST['invite'])) {
        $invite = '';
    } else {
        $invite = $_POST['invite'];
    }
    if ($user->register($_POST['blogName'], $_POST['password'], $_POST['emailAddress'], $_POST['birthday'], $invite)) {
        
        $data['code'] = 'REGISTER_SUCCESS';
        $data['message'] = "Success! You now have an account.";
        $sessionObj = new Session();
        if ($sessionObj->createSession($user->ID, $user->mainBlog) !== false) {
            $sessionID =  $sessionObj->sessionID; // Automatically log them in
        } else {
            $sessionID = 'null';
        }
    }
}

echo json_encode($data);