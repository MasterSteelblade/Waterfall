<?php

require_once(__DIR__.'/../../src/loader.php');
$sessionObj = new Session();

if (isset($_COOKIE['waterfall'])) {

    if ($sessionObj->getSession($_COOKIE['waterfall'])) {
        // It's a valid session. We call updateSession() to reset the expiry date. 
        $sessionObj->updateSession();
        $session = true;
    } else {
        $session = false;
    }
} else {
    $sessionObj->createSession();
    $session = false;
}

// Now we can just check if session is false when we need to verify its state.
