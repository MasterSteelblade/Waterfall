<?php 
require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');

if (isset($_POST['email'])) {
    $user = new User();
    $user->getByEmail($_POST['email']);
    if (!$user->failed) {
        $user->requestPasswordReset();
    }
    $data = array('code' => 'SUCCESS');
    echo json_encode($data);
}