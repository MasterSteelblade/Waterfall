<?php

require_once(__DIR__.'/../../../src/loader.php');
require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');


$data = array();
if (!isset($_POST['email']) || !isset($_POST['verify']) || !isset($_POST['password']) || !isset($_POST['confirmPassword'])) {
    $data['code'] = 'ERR_MISSING_INFO';
    $data['message'] = "Missing info! Make sure you filled everything in.";
    echo json_encode($data);
    exit();
}

if ($_POST['password'] != $_POST['confirmPassword']) {
    $data['code'] = 'ERR_PASSWORD_MISMATCH';
    $data['message'] = "The passwords didn't match!";
    echo json_encode($data);
    exit();
}

$user = new User();
$user->getByEmail($_POST['email']);
if ($user->failed) {
    $data['code'] = 'ERR_INVALID_USER';
    $data['message'] = "Invalid user";
    echo json_encode($data);
    exit();
}

if ($user->verifyKey != $_POST['verify']) {
    $data['code'] = 'ERR_BAD_VERIFY';
    $data['message'] = "The verify key was bad.";
    echo json_encode($data);
    exit();
}

if (strlen($_POST['password']) < 6) {
    $data['code'] = 'ERR_PASSWORD_SHORT';
    $data['message'] = "Password too short! Needs to be at least six characters.";
    echo json_encode($data);
    exit();
}

if ($user->resetPassword($_POST['password'])) {
    $data['code'] = 'SUCCESS';
    $data['message'] = "Password changed!";
    echo json_encode($data);
} else {
    $data['code'] = 'ERR_FAILURE';
    $data['message'] = "Unknown failure";
    echo json_encode($data);

}