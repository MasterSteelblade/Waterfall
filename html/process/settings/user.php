<?php 

require_once(__DIR__.'/../../includes/session.php');
require_once(__DIR__.'/../../../lang/langList.php');
header('Content-type: application/json');
$data = array();
use EasyCSRF\Exceptions\InvalidCsrfTokenException;
// Force this to be a JSON return for a laugh
$easyCSRF = new EasyCSRF\EasyCSRF($sessionObj);
try {
    $easyCSRF->check($sessionObj->sessionData['csrfName'], $_POST['tokeItUp'], 60*15, true);
} catch(InvalidCsrfTokenException $e) {
    $data['code'] = L::error_csrf;
    echo json_encode($data);
    exit();
}
if ($session !== false) {
    $user = $sessionObj->user;
    if (isset($_POST['currentPassword']) && $_POST['currentPassword'] != '') {
        $passwordConfirmed = $user->confirmPassword($_POST['currentPassword']);
    }
    if (isset($_POST['pronouns'])) {
        $user->updatePronoun(substr($_POST['pronouns'], 0, 20));
    }
    if (isset($_POST['dashTheme'])) {
        $user->setTheme($_POST['dashTheme']);
    }
    if (isset($_POST['newPassword']) && $_POST['newPassword'] != '') {
        if ($passwordConfirmed == false) {
            $data['code'] = 'ERROR_BAD_PASSWORD';
            $data['message'] = L::error_missing_pw_confirm;
            echo json_encode($data);
            exit();
        }
        if (strlen($_POST['newPassword']) < 6) {
            $data['code'] = 'ERROR_PASSWORD_SHORT';
            $data['message'] = L::error_new_pw_short;
            echo json_encode($data);
            exit();
        } elseif ($_POST['newPassword'] != $_POST['confirmPassword']) {
            $data['code'] = 'ERROR_PASSWORD_MISMATCH';
            $data['message'] = L::error_new_pw_mismatch;
            echo json_encode($data);
            exit();
        } else {
            if ($user->updatePassword($_POST['newPassword']) == false) {
                $data['code'] = 'ERROR_BACKEND_FAILURE';
                $data['message'] = L::error_unknown;
                echo json_encode($data);
                exit();
            }
        }
    }
    if (isset($_POST['emailAddress']) && $_POST['emailAddress'] != '' && $_POST['emailAddress'] != $user->email) {
        if ($passwordConfirmed == false) {
            $data['code'] = 'ERROR_BAD_PASSWORD';
            $data['message'] = L::error_missing_pw_confirm;
            echo json_encode($data);
            exit();
        }
        if (filter_var($_POST['emailAddress'], FILTER_VALIDATE_EMAIL) == false) {
            $data['code'] = 'ERROR_INVALID_EMAIL';
            $data['message'] = L::error_invalid_email;
            echo json_encode($data);
            exit();
        }
        if ($user->updateEmail($_POST['emailAddress']) == false) {
            $data['code'] = 'ERROR_EMAIL_TAKEN';
            $data['message'] = L::error_email_in_use;
            echo json_encode($data);
            exit();
        }
    }
    // Main blog
    if (isset($_POST['mainBlog'])) {
        $blog = new Blog;
        $blog->getByBlogName($_POST['mainBlog']);
        if (!$blog->failed) {
            if ($blog->ownerID == $user->ID) {
                // Add  || $blog->checkMemberPermission($sessionObj->user->ID, 'is_member') into the above if we want to allow switching to group blogs
                $user->switchMainBlog($blog->ID);
            }
        }
    }
    // Checkboxes just use isset
    // Dyslexia Font
    if (isset($_POST['useDyslexiaFont']) && $_POST['useDyslexiaFont'] == 'true') {
        $user->settings['accessibility']['dyslexiaFont'] = true;
    } else {
        $user->settings['accessibility']['dyslexiaFont'] = false;
    }

    if (isset($_POST['useLargeFont']) && $_POST['useLargeFont'] == 'true') {
        $user->settings['accessibility']['largeFont'] = true;
    } else {
        $user->settings['accessibility']['largeFont'] = false;
    }
    if (isset($_POST['omniDash']) && $_POST['omniDash'] == 'true') {
        $user->settings['omniDash'] = true;
    } else {
        $user->settings['omniDash'] = false;
    }
    if (isset($_POST['viewNSFW']) && $_POST['viewNSFW'] == 'true') {
        $user->settings['viewNSFW'] = true;
    } else {
        $user->settings['viewNSFW'] = false;
    }
    if (isset($_POST['showFeatures']) && $_POST['showFeatures'] == 'true') {
        $user->settings['showFeatures'] = true;
    } else {
        $user->settings['showFeatures'] = false;
    }
    if (isset($_POST['mutualActivity']) && $_POST['mutualActivity'] == 'true') {
        $user->settings['mutualActivity'] = true;
    } else {
        $user->settings['mutualActivity'] = false;
    }
    if (isset($_POST['ocOnly']) && $_POST['ocOnly'] == 'true') {
        $user->settings['ocOnly'] = true;
    } else {
        $user->settings['ocOnly'] = false;
    }
    if (isset($_POST['showNaughtyFeatures']) && $_POST['showNaughtyFeatures'] == 'true') {
        $user->settings['explicitFeatures'] = true;
    } else {
        $user->settings['explicitFeatures'] = false;
    }

    // Language settings
    if (isset($_POST['userLanguage']) && $_POST['userLanguage'] != '') {
        $language = trim($_POST['userLanguage']);
        if (array_key_exists($language, getLanguageNames())) {
            $user->settings['language'] = $language;
            setcookie('lang', $language, array(
                'expires' => time() + 2592000,
                'path' => '/',
                'domain' => $_ENV['COOKIE_URL'],
                'secure' => true,
                'samesite' => 'lax',
            ));
        }
    }    

    // Main Blog
    if (isset($_POST['switchMainBlog'])) {
        $user->switchMainBlog($_POST['switchMainBlog']);
    }
    if (isset($_POST['emailFollows']) && $_POST['emailFollows'] == 'true') {
        $user->settings['email']['follows'] = true;
    } else {
        $user->settings['email']['follows'] = false;
    }
    if (isset($_POST['emailNews']) && $_POST['emailNews'] == 'true') {
        $user->settings['email']['news'] = true;
    } else {
        $user->settings['email']['news'] = false;
    }
    if (isset($_POST['emailAsks']) && $_POST['emailAsks'] == 'true') {
        $user->settings['email']['asks'] = true;
    } else {
        $user->settings['email']['asks'] = false;
    }
    if (isset($_POST['emailPromos']) && $_POST['emailPromos'] == 'true') {
        $user->settings['email']['promos'] = true;
    } else {
        $user->settings['email']['promos'] = false;
    }
    if (isset($_POST['emailMentions']) && $_POST['emailMentions'] == 'true') {
        $user->settings['email']['mentions'] = true;
    } else {
        $user->settings['email']['mentions'] = false;
    }
    if (isset($_POST['emailParticipation']) && $_POST['emailParticipation'] == 'true') {
        $user->settings['email']['participation'] = true;
    } else {
        $user->settings['email']['participation'] = false;
    }

    if (isset($_POST['tagBlacklist'])) {
        $blacklist = explode(',', $_POST['tagBlacklist']);
        $user->tagBlacklist = array();
        foreach ($blacklist as $tag) {
            $tag = trim($tag);
            $user->tagBlacklist[] = $tag;
        }
        $user->updateTagBlacklist();
    }
    if ($user->updateSettings()) {
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_updated;
    } else {
        $data['code'] = 'FAILURE';
        $data['message'] = L::error_unknown;
    }
}
echo json_encode($data);
