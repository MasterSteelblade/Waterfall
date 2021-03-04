<?php 

require_once(__DIR__.'/../../includes/session.php');

if ($session == false) {
    header('Content-type: application/json');

    $data['code'] = 'NO_SESSION';
    echo json_encode($data);
    exit();
}

$post = new Post($_POST['pollID']);
if ($post->failed) {
    header('Content-type: application/json');

    $data['code'] = 'ERR_INVALID_POST';
    echo json_encode($data);
    exit();
}

$poll = new Poll($post->pollID);

if ($poll->canVote($sessionObj->user->ID) == false) {
    header('Content-type: application/json');
    $poll->render($post->ID, $sessionObj->sessionData['activeBlog']);

    exit();
}

if ($poll->registerVote($_POST['selected'], $sessionObj->user->ID)) {
    $poll->render($post->ID, $sessionObj->sessionData['activeBlog']);

} // This line will need changing when multiple options are implemented. 
 else {
    header('Content-type: application/json');

     $data['code'] = 'ERR_VOTE_FAILED';
     echo json_encode($data);
     exit();
 }