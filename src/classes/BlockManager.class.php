<?php 


class BlockManager {

    /** A handler for checking whether someone blocked someone else.
     * 
     * Initialise with a userID, typically the person who's perusing the
     * blog/site. If they're not logged in, intialise that to 0, let the wfuuid
     * do the work if it's set. 
     * 
     * If the wfuuid ISN'T set, it'll fall back on the IP address. 
     * 
     */

    public $user;
    public $uuid;
    public $IP;
    public $myBlockedUsers = array();

    public function __construct(int $userID = 0) {
        if ($userID == 0) {
            return true;
        }
        $this->user = new User($userID);
        if ($this->user->failed == true) {
            return false;
        } else {
            return true;
        }
    }

    public function hasBlockedUser($userID) {
        if ($userID == 0) {
            return false;
        }
        if (in_array($userID, $this->user->blockedUsers)) {
            return true;
        } else {
            return false;
        }
    }

}