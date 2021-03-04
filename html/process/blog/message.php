<?php 

require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');

header('Content-type: application/json');


$data = array();
if ($session == false) {
    $data['code'] = 'ERR_GENERIC_FAILURE';
} else {
    // I'm going to kill myself because of this but thank god the API 
    // version had this mostly done
    $myBlog = new Blog($sessionObj->sessionData['activeBlog']);
    if ($myBlog->failed) {
        $data['code'] = 'ERR_YOUR_BLOG_NOT_FOUND';
        echo json_encode($data);
        exit();
    } else {
        $messageText = $_POST['messageText'];
        $recipientName = $_POST['recipient'];
        $recipientBlog = new Blog();
        $recipientBlog->getByBlogName($_POST['recipient']);
        if ($recipientBlog->failed) {
            $data['code'] = 'ERR_RECIPIENT_BLOG_NOT_FOUND';
            echo json_encode($data);
            exit();
        } else {
            $recipientID = $recipientBlog->ID;
        }
        
        if (isset($_POST['sender'])) {
            $senderName = $_POST['sender'];
            $levelThreeAsk = false;
            $senderBlog = new Blog();
            $senderBlog->getByBlogName($senderName);
            if ($senderBlog->failed) {
                $data['code'] = 'ERR_YOUR_BLOG_NOT_FOUND';
                echo json_encode($data);
                exit();
            } else {
                if ($senderBlog->ownerID != $sessionObj->user->ID && !$senderBlog->checkMemberPermission($sessionObj->user->ID, 'send_asks')) {
                    $data['code'] = 'ERR_NOT_YOUR_BLOG';
                    echo json_encode($data);
                    exit();
                }
                // ======================
                // PERMISSION CHECKS HERE
                // ======================
                $senderID = $senderBlog->ID;
            }
        } else {
            $levelThreeAsk = true; // Signifies the user was logged out
            $senderID = null;
        }
        if ((isset($_POST['anon']) && $_POST['anon'] == 'true') || $levelThreeAsk == true) {
            $isAnon = true;
        } else {
            $isAnon = false;
        }
        // ============================
        // Insert ask level checks here
        // ============================

        $blogOwnerBlockCheck = new BlockManager($recipientBlog->ownerID);
        $myBlogCheck = new BlockManager($senderBlog->ownerID);
        if ($blogOwnerBlockCheck->hasBlockedUser($senderBlog->ownerID) || $myBlogCheck->hasBlockedUser($recipientBlog->ownerID)) {
            $data['code'] = 'ERR_RECIPIENT_BLOG_NOT_FOUND'; // Lie and say it doesn't exist. 
            echo json_encode($data);
            exit();  
        }

        if ($recipientBlog->askLevel == 0) {
            $data['code'] = 'ERR_ASK_LEVEL_NOT_ACCEPTING';
            echo json_encode($data);
            exit();  
        }
        if ($levelThreeAsk && $recipientBlog->askLevel != 3) {
            $data['code'] = 'ERR_ASK_LEVEL_NO_LOGGED_OUT';
            echo json_encode($data);
            exit();
        }
        if ($isAnon && $recipientBlog->askLevel == 1) {
            $data['code'] = 'ERR_ASK_LEVEL_NO_ANON';
            echo json_encode($data);
            exit();
        }
        $message = new Message();
        $message->content = WFText::makeTextSafe($messageText);
        $message->sender = $senderID;
        $message->recipient = $recipientID;
        $message->anon = $isAnon;
        if ($levelThreeAsk) {
            $message->deletedOutbox = true;
        }
        $message->type = 'ask';
        if ($message->saveToDatabase()) {
            $data['code'] = 'SUCCESS';
        } else {
            $data['code'] = 'ERR_COULD_NOT_SAVE';
        }
    } 
}

echo json_encode($data);
exit();