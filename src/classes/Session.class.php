<?php 

/**
 * @class Session
 * @brief Handles sessions for the API.
 *
 * Handles all session related methods.
 * @author Benjamin Clarke
*/

use EasyCSRF\Interfaces\SessionProvider;

class Session implements SessionProvider {
    private $redis;
    public $sessionID;
    public $sessionData = array('userID' => 0, 'activeBlog' => 0, 'blogLogins' => array());
    public $user; 
    public $userIsValid = true;
    public $userMissing = array();
    public $sessionMapRedis;
    public $JSON;
    

    public function __construct() {
        $this->redis = new WFRedis('sessions');
        $this->sessionMapRedis = new WFRedis('session_map');
    }

    public function createSession() {

        /**
         * Creates a new session. 
         * 
         * @param userID The ID of the user in the database to create the session for. 
         * @param activeBlog The ID of the blog that's active at initialisation. Usually their main blog.
         */
        /**
         * Session map uses session IDs as keys. To clear a user's sessions, search for the user's ID 
         * and delete those keys from both the map and the session database itself.
         */
        $this->sessionID = WFUtils::generateSessionID();

        $this->JSON = json_encode($this->sessionData);
        if (!$this->redis->set(strval($this->sessionID), strval($this->JSON))) {
            return false;
        }


        $this->redis->expireIn(strval($this->sessionID), intval(time() + 2592000));
        setcookie('waterfall', $this->sessionID, array('expires' => time()+2592000, 'path' => '/', 'domain' => $_ENV['COOKIE_URL'], 'secure' => true, 'samesite' => 'lax', 'httponly' => true));
        $this->set('csrfName', WFUtils::generateRandomString(16));
        return $this->sessionID;
    }

    public function sessionLogin($userID, $activeBlog) {
        $this->sessionData['userID'] = $userID;
        $this->sessionData['activeBlog'] = $activeBlog;
        $this->sessionData['blogLogins'] = array();
        $this->sessionMapRedis->set(strval($this->sessionID), strval($this->sessionData['userID']));
        $this->sessionMapRedis->expireIn(strval($this->sessionID), intval(time() + 2592000));
        $this->user = new User($this->sessionData['userID']);

        if ($this->updateSession()) {
            return $this->sessionID;
        } else {
            return false;
        }
    }

    public function getSession($sessionID) {
        /**
         * Gets a session. 
         * 
         * @param sessionID from cookie or somesuch.
         */
        $sessionData = $this->redis->get($sessionID);
        if ($sessionData != false) {
            $this->sessionID = $sessionID;
            $this->sessionData = json_decode($sessionData, true);

            $this->sessionMapRedis->expireIn(strval($this->sessionID), intval(time() + 2592000));
            $this->redis->expireIn(strval($this->sessionID), intval(time() + 2592000));
            if (!isset($this->sessionData['csrfName'])) {
                $this->set('csrfName', WFUtils::generateRandomString(16));
            }
            if (isset($this->sessionData['userID']) && $this->sessionData['userID'] != 0 && $this->sessionData['activeBlog'] != 0) {
                // We can get the currently active user here to save database calls later. 
                $this->user = new User($this->sessionData['userID']);
                if ($this->user->failed == true) {
                    $this->destroySession();
                    return false;
                }
                $this->user->updateVisit();
                $this->verifyUser();
                $this->updateSession();
                return true;

            } else {

                return false;
            }
        } else {
            $this->createSession();
            return false;
        }
    }

    public function updateSession($sendCookie = true) {
        /**
         * Updates and renews a session. Call this after changing any data, or to renew the cookies. 
         */
        $this->JSON = json_encode($this->sessionData);
        if ($this->redis->set(strval($this->sessionID), strval($this->JSON)) !== false) {
            $this->sessionMapRedis->expireIn(strval($this->sessionID), intval(time() + 2592000));
            $this->redis->expireIn(strval($this->sessionID), intval(time() + 2592000));
            
            if ($sendCookie) {
                setcookie('waterfall', $this->sessionID, array('expires' => time()+2592000, 'path' => '/', 'domain' => $_ENV['COOKIE_URL'], 'secure' => true, 'samesite' => 'lax', 'httponly' => true));
            
                if (isset($this->sessionData['userID']) && $this->sessionData['userID'] != 0) {
                    setcookie('wfuuid', $this->user->UUID, array('expires' => time()+2592000, 'path' => '/', 'domain' => $_ENV['COOKIE_URL'], 'secure' => true, 'samesite' => 'lax', 'httponly' => true));

                    $tagBlacklist = implode('|', $this->user->tagBlacklist);
                    $tagBlacklist = str_replace("\'", "'", $tagBlacklist);
                    $tagBlacklist = str_replace('\"', '"', $tagBlacklist);
                    setcookie('wf_tagblacklist', strtolower($tagBlacklist), array('expires' => time()+2592000, 'path' => '/', 'domain' => $_ENV['COOKIE_URL'], 'secure' => true, 'httponly' => false));
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function destroySession() {
        /**
         * Destroys a session and removes the session cookie.
         */
        if ($this->redis->del(strval($this->sessionID)) !== false && $this->sessionMapRedis->del(strval($this->sessionID))) {
            setcookie('waterfall',$this->sessionID,1,'/',$_ENV['COOKIE_URL'], true);
            return true;
        } else {
            return false;
        }
    }

    public function verifyUser() {
        /**
         * Verify the user's data. If nothing is wrong, we don't need to do anything, since the value defaults to true. 
         * This can be used to make sure that all data the user needs is present.
         */
        if ($this->user->DOB == null) {
            $this->userIsValid = false;
            $this->userMissing[] = 'birthday';
        }
    }

    public function get($key) {
        return $this->sessionData[$key];
    }

    public function set($key, $value) {
        $this->sessionData[$key] = $value;
        $this->updateSession(false);
    }
}