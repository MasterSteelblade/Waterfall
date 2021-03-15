<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
use EasyCSRF\Exceptions\InvalidCsrfTokenException;
// Force this to be a JSON return for a laugh
$easyCSRF = new EasyCSRF\EasyCSRF($sessionObj);
if ($session != false) {
    try {
        $easyCSRF->check($sessionObj->sessionData['csrfName'], $_POST['tokeItUp'], 60*15, true);
    } catch(InvalidCsrfTokenException $e) {
        $data['code'] = 'ERR_CSRF_FAILURE';
        $data['message'] = "CSRF failure! Please refresh and try again.";
        echo json_encode($data);
        exit();
    }
    $user = $sessionObj->user;
    $blog = new Blog();
    if ($blog->getByBlogName($_POST['editingBlog'])) {
        // It worked. 
        if ($blog->ownerID == $user->ID  || $blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings')) {
            // It's the owner! We can change shit. 
            if (isset($_POST['blogTheme'])) {
                $blog->setTheme($_POST['blogTheme']);
            }
            if (isset($_POST['adultOnly']) && $_POST['adultOnly'] == 'true') {
                $blog->markNSFW();
            } else {
                $blog->unmarkNSFW();
            }
            if (isset($_POST['showPronouns']) && $_POST['showPronouns'] == 'true') {
                $blog->settings['showPronouns'] = true;
            } else {
                $blog->settings['showPronouns'] = false;

            }
            if (isset($_POST['blogPass']) && $_POST['blogPass'] != '') {
                $blog->setPassword($_POST['blogPass']);
            }
            if (isset($_POST['blogName']) && strtolower($_POST['blogName']) != $blog->blogName) {
                $blogChangeSuccess = $blog->updateBlogName($_POST['blogName']);
                if (!$blogChangeSuccess) {
                    $data['code'] = 'ERR_BLOG_TAKEN';
                    $data['message'] = "This blog name is already in use.";
                    echo json_encode($data);
                    exit();
                }
            }
            if (isset($_FILES['avatar'])) {
                $database = Postgres::getInstance();
                $imageFile = $_FILES['avatar'];
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                $server = WFUtils::pickServer();
                $url = $server.'/image/add';
                curl_setopt($ch, CURLOPT_URL, $url);
                $postData = array();
                $postData['images'] = new CurlFile($imageFile['tmp_name']);
                $postData['isAvatar'] = '1';
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch,CURLOPT_TIMEOUT,1000);
                $chResponse = curl_exec($ch);
                $json = json_decode($chResponse, true);
                unset($data);
                $data = array();
                if ($json['status'] != 'success') {
                    // It failed, exit
                    $data['code'] = 'ERR_NOT_IMAGE';
                    $data['message'] = "Your avatar wasn't an image file.";
                    echo json_encode($data);
                    exit();
                }
                if (isset($json['imgData'])) {
                    $data = $json['imgData'];
                    $onServer = array($json['onServer']);
                    $values = array(json_encode($data), false, $database->php_to_postgres($onServer));
                    $imageID = $database->db_insert("INSERT INTO images (paths, is_art, servers, version) VALUES ($1,$2,$3,2)", $values);
                    $blog->updateAvatar($imageID);
                }
            }
            if (!isset($_POST['blogTitle']) || $_POST['blogTitle'] == '') {
                $blogTitle = 'Untitled';
            } else {
                $blogTitle = WFText::makeTextSafe($_POST['blogTitle']);
            }
            if (!isset($_POST['queueTag']) || $_POST['queueTag'] == '') {
                $blog->settings['queueTag'] = '';
            } else {
                $blog->settings['queueTag'] = $_POST['queueTag'];
            }
            if (isset($_POST['badgeString'])) {
                $blog->updateBadges($_POST['badgeString']);
            }
            // Ask levels are stupid. I couldn't think of decent enum names. 
            // 0 = Off, 1 = No anons, 2 = Anon but only logged in, 3 = Everything
            if (isset($_POST['askLevel'])) {
                if (intval($_POST['askLevel']) < 4) {
                    $blog->setAskLevel(intval($_POST['askLevel']));
                } else {
                    $blog->setAskLevel(0);
                }
            }
            $blog->updateBlogTitle($blogTitle);
            if (!isset($_POST['blogDescription'])) {
                $blogDesc = '';
            } else {
                $blogDesc = WFText::makeTextSafe($_POST['blogDescription']);
            }
            $blog->updateBlogDescription($blogDesc);
            if (isset($_POST['blogName']) && $_POST['blogName'] != '') {
                $blog->updateBlogName($_POST['blogName']);
            }
            if (isset($_POST['queueTag'])) {
                $blog->settings['queueTag'] = $_POST['queueTag'];
            }
            if (isset($_POST['queueStart'])) {
                if (is_numeric($_POST['queueStart']) && $_POST['queueStart'] <= 24 && $_POST['queueStart'] >= 0) {
                    $blog->settings['queueRangeStart'] = intval($_POST['queueStart']);
                } else {
                    $blog->settings['queueRangeStart'] = intval(0);
                }
            }
            if (isset($_POST['queueEnd'])) {
                if (is_numeric($_POST['queueEnd']) && $_POST['queueEnd'] < 24 && $_POST['queueEnd'] >= 0 && intval($_POST['queueEnd']) != $blog->settings['queueRangeStart']) {
                    $blog->settings['queueRangeEnd'] = intval($_POST['queueEnd']);
                } else {
                    $blog->settings['queueRangeEnd'] = intval(23);
                }
            }
            if (isset($_POST['queueFreq']) && is_numeric($_POST['queueFreq'])) {
                if (intval($_POST['queueFreq']) < 0 ) {
                    $blog->settings['queueFrequency'] = 0;
                } else if (intval($_POST['queueFreq']) > 100) {
                    $blog->settings['queueFrequency'] = 100;
                } else {
                    $blog->settings['queueFrequency'] = intval($_POST['queueFreq']);
                }
            }
            $blog->updateSettings();
            $data['code'] = 'SUCCESS';
            $data['message'] = "Success!";
            echo json_encode($data);

        }
    }
}