<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $invite = new BlogMember($_POST['inviteID']);
    if ($invite->failed) {
        $data['code'] = 'ERR_PERMISSIONS';
        $data['message'] = L::error_invalid_permissions;
        echo json_encode($data);
    } else {
        if ($invite->userID == $sessionObj->user->ID) {
            $invite->confirmInvite();
            $data['code'] = 'SUCCESS';
            $data['message'] = L::string_success;
            echo json_encode($data);
        } else {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = L::error_invalid_permissions;
            echo json_encode($data);
        }
    }
}