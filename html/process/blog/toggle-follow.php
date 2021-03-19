<?php 

require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');

header('Content-type: application/json');


$data = array();
if ($session == false) {
    $data['code'] = 'ERR_GENERIC_FAILURE';
} else {
    $myBlog = new Blog($sessionObj->sessionData['activeBlog']);
    if ($myBlog->failed) {
        $data['code'] = 'ERR_YOUR_BLOG_NOT_FOUND';
        $data['message'] = L::error_no_session;
        echo json_encode($data);
        exit();
    } 
    $toFollow = new Blog();
    $toFollow->getByBlogName($_POST['blogName']);
    if ($toFollow->failed) {
        $data['code'] = 'ERR_BLOG_TO_FOLLOW_NOT_FOUND';
        $data['message'] = L::error_follow_could_not_find;
        echo json_encode($data);
        exit();
    }
    //$blogOwnerBlockCheck = new BlockManager($recipientBlog->ownerID);
    //$myBlogCheck = new BlockManager($senderBlog->ownerID);
    //if ($blogOwnerBlockCheck->hasBlockedUser($senderBlog->ownerID) || $myBlogCheck->hasBlockedUser($recipientBlog->ownerID)) {
    //    $data['code'] = 'ERR_BLOG_TO_FOLLOW_NOT_FOUND';
    //    echo json_encode($data);
    //    exit();
    //}
    $following = $myBlog->checkForFollow($toFollow->ID);
    if (!$following) {
        $myBlog->addFollow($toFollow->ID);
        $data['code'] = 'SUCCESS';
        $data['newFollowText'] = L::string_unfollow;
    } else {
        $myBlog->removeFollow($toFollow->ID);
        $data['code'] = 'SUCCESS';
        $data['newFollowText'] = L::string_follow;
    }
}

echo json_encode($data);
exit();