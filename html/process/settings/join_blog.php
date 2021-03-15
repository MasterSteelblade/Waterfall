<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $invite = new BlogMember($_POST['inviteID']);
    if ($invite->failed) {
        $data['code'] = 'ERR_PERMISSIONS';
        $data['message'] = "You don't have permission to do that.";
        echo json_encode($data);
    } else {
        if ($invite->userID == $sessionObj->user->ID) {
            $invite->confirmInvite();
            $data['code'] = 'SUCCESS';
            $data['message'] = "Success!";
            echo json_encode($data);
        } else {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = "You don't have permission to do that.";
            echo json_encode($data);
        }
    }
}