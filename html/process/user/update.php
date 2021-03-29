<?php 

require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');

header('Content-type: application/json');


$data = array();
if ($session == false) {
    $data['code'] = 'ERR_GENERIC_FAILURE';
    $data['message'] = L::error_unknown;
} else {
    if (isset($_POST['birthday'])) {
        try {
            $timestamp = strtotime($_POST['birthday']);
            $date = new DateTime();
            $date->setTimestamp($timestamp);
        } catch (Exception $e) {
            $data['code'] = 'ERR_INVALID_DATE1';
            $data['message'] = L::error_invalid_date;
            echo json_encode($data);
            exit();
        }
        $now = new DateTime();
        $age = $now->diff($date);
        $y = $age->y;
        if ($y < 13) {
            $data['code'] = 'ERR_INVALID_DATE2';
            $data['message'] = L::error_too_young;
            echo json_encode($data);
            exit();
        }
        $dForm = $date->format('Y-m-d');
        if (!$sessionObj->user->updateBirthday($dForm)) {
            $data['code'] = 'ERR_INVALID_DATE3';
            $data['message'] = L::error_invalid_date;
            echo json_encode($data);
            exit();
        } else {
            $data['code'] = 'SUCCESS';
            $data['message'] = L::string_success;
        }
    }
    $data['code'] = 'SUCCESS';
    $data['message'] = L::string_success;
}
    echo json_encode($data);
    exit();