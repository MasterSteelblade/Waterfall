<?php 

require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');

header('Content-type: application/json');


$data = array();
if ($session == false) {
    $data['code'] = 'ERR_GENERIC_FAILURE';
} else {
    if (isset($_POST['birthday'])) {
        try {
            $date = new DateTime($_POST['birthday']);
        } catch (Exception $e) {
            $data['code'] = 'ERR_INVALID_DATE';
            echo json_encode($data);
            exit();
        }
        $now = new DateTime();
        $age = $now->diff($date);
        $y = $age->y;
        if ($y < 13) {
            $data['code'] = 'ERR_INVALID_DATE';
            echo json_encode($data);
            exit();
        }
        $dForm = $date->format('Y-m-d');
        if ($sessionObj->user->updateBirthday($dForm)) {
            $data['code'] = 'ERR_INVALID_DATE';
            echo json_encode($data);
            exit();
        }
    }
    $data['code'] = 'SUCCESS';
}
    echo json_encode($data);
    exit();