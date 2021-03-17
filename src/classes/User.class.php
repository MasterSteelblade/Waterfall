<?php 

class User{

        /**
         * Waterfall's User class. Contains all functions relating to users. 
         */

    private $database;
    public  $ID;
    public $theme;
    public string $email;
    public string $password;
    public int $mainBlog;
    public $banned;
    public $banReason;
    public $verified;
    public $tagBlacklist;
    public $registeredAt;
    public $lastVisit;
    public $UUID;
    public $DOB;
    private $sessionKey;
    public $verifyKey;
    public $lastIP;
    public $blockedUsers;
    public $lastInboxTime = 0;
    public $secretKey;
    public $timezone;
    public $modLevel;
    public $subscriptionTier;
    public $subscriptionEnds;
    public $badgesAllowed = array();
    public $themesAllowed = array();
    public $stripeCustomerID;
    public $blogIDs;
    public $groupBlogIDs;
    public $blogs;
    public $groupBlogs = array();
    public $settings = array('omniDash' => false, 'showFeatures' => true, 'postDepth' => 5, 'viewNSFW' => false, 'commissionMarketTOSAccepted' => false, 'explicitFeatures' => false, 'email' => array('follows' => true, 'news' => true, 'asks' => true, 'promos' => true, 'mentions' => true, 'participation' => false), 'accessibility' => array('dyslexiaFont' => false, 'showImageID' => false));
    public $restrictions;
    public $failed = false;
    public $pronouns;

    public function __construct($ID = 0) {
        /** 
         * Constructor class. Gets a user by ID if one is specified. 
         * 
         * @param ID Integer, the ID of the user row in the database. 
         */
        $this->database = Postgres::getInstance();
        if (intval($ID) != 0) { // We assume we're making an empty user if the ID is 0, if not, check for a user
            $values = array(intval($ID)); // For safety purposes
            $result = $this->database->db_select("SELECT * FROM users WHERE id = $1", $values);
            if ($result) {
            $row = $result[0];
            $this->ID = $row['id'];
            $this->theme = $row['dashboard_theme'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->mainBlog = $row['main_blog'];
            $this->accountType = $row['account_type'];
            $this->verified = $row['verified'];
            $this->tagBlacklist = $this->database->postgres_to_php($row['tag_blacklist']);
            $this->registeredAt = new DateTime($row['registered_at']);
            $this->lastVisit = new DateTime($row['last_visit']);
            $this->UUID = $row['uuid'];
            if ($row['date_of_birth'] != null) {
                $this->DOB = new DateTime($row['date_of_birth']);
            } else {
                $this->DOB = null;
            }
            #$this->sessionKey = $row['session_key'];
            $this->lastIP = $row['last_ip'];
            if ($row['blocked_users'] != '{}' && $row['blocked_users'] != null) {
                $this->blockedUsers = $this->database->postgres_to_php($row['blocked_users']);
            } else {
                $this->blockedUsers = array();
            }            
            if ($row['inbox_last_read'] != null) {
                $this->lastInboxTime = $row['inbox_last_read'];
            }
            $this->secretKey = $row['secret_key'];
            $this->timezone = $row['timezone'];
            $this->modLevel = $row['mod_level'];
            $this->verifyKey = $row['verify_key'];
            $this->getAllowedBadges($this->database->postgres_to_php($row['badges_allowed']));
            $this->getAllowedThemes($this->database->postgres_to_php($row['themes_allowed']));
            $this->subscriptionTier = $row['subscription_tier'];
            $this->pronouns = $row['pronouns'];
            if ($row['customer_id'] != null) {
                $this->stripeCustomerID = $row['customer_id'];
            }          
            if ($row['settings'] == '' || $row['settings'] == null) {
                // Nothing
            } else {
                $this->settings = json_decode($row['settings'], true); // True here gives us an associative array
            }
            $this->subscriptionEnds = new DateTime($row['subscription_ends']);
            $this->restrictions = $this->database->postgres_to_php($row['restrictions']);
            $this->userFlags = $this->database->postgres_to_php($row['flags']);
            $this->getUserBlogIDs();

            $this->getUserBlogs();
            $this->getUserGroupBlogs();
            return $this->ID;
            } else {
                $this->failed = true;
                return false;
            }
        } else {
            return true; // Remember to use triple === though realistically, how the fuck is creating an empty object going to fail
        }
    }

    public function hasTwoFactor() {
        if ($this->secretKey == null || $this->secretKey == '') {
            return false;
        } else {
            return true;
        }
    }

    public function markVerified() {
        $values = array($this->ID, true, '');
        $res = $this->database->db_update("UPDATE users SET verified = $2, verify_key = $3 WHERE id = $1", $values);
    }

    public function disableTwoFactor() {
        $values = array($this->ID);
        $result = $this->database->db_update("UPDATE users SET secret_key = null WHERE id = $1", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function enableTwoFactor($secretKey, $totpCode) {
        /**
         * Enables two-factor authentication for the user.
         * 
         * We assume that the `secretKey` parameter is an upper-case
         * Base32-encoded secret, and verify the given `totpCode` using
         * that secret. If the TOTP code is valid, we save the secret.
         *
         * @param secretKey The base32-encoded TOTP secret key
         * @param totpCode A 6-digit TOTP code
         */

        // First, check the given TOTP code is currently valid for this secretKey.
        // Return `false` if the TOTP code is invalid.
        $ga = new \Steelblade\GoogleAuthenticator\GoogleAuthenticator();
        if (!$ga->checkCode($secretKey, $totpCode)) {
            return false;
        }

        // The code is valid, store the secretKey
        $values = array($this->ID, $secretKey);
        $result = $this->database->db_update("UPDATE users SET secret_key = $2 WHERE id = $1", $values);

        // Return `true` if the database update worked
        if ($result) {
            return true;
        } else {
            return 0;
        }
    }

    public function verifyTwoFactor($totpCode) {
        /**
         * Verifies the given TOTP code against this user, if they have
         * two-factor authentication enabled. Returns `true` if the user
         * does not have two-factor authentication enabled.
         *
         * @param totpCode A 6-digit TOTP code
         */

        if (!$this->hasTwoFactor()){
            // return success if 2FA is not enabled
            return true;
        }

        $ga = new \Steelblade\GoogleAuthenticator\GoogleAuthenticator();
        return $ga->checkCode($this->secretKey, $totpCode);
    }

    public function switchMainBlog($blogID) {
        /**
         * Switches a user's main blog. 
         * 
         * @param blogID The ID of the blog to switch to.
         */
        if (!in_array($blogID, $this->blogIDs)) {
            return false;
        }
        $this->mainBlog = $blogID;
        $values = array($this->mainBlog, $this->ID);
        $result = $this->database->db_update("UPDATE users SET main_blog = $1 WHERE id = $2", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function hasBlockedUser($userID) {
        if (in_array($userID, $this->blockedUsers)) {
            return true;
        } else {
            return false;
        }
    }

    public function unblock($userID) {
        if (($key = array_search($userID, $this->blockedUsers)) !== false) {
            unset($this->blockedUsers[$key]);
        }
        $values = array($this->ID, $this->database->php_to_postgres($this->blockedUsers));
        $result = $this->database->db_update("UPDATE users SET blocked_users = $2 WHERE id = $1", $values);
        if ($result) {
            $blockedUser = new User($userID);
            $blockedBlogs = $blockedUser->blogs;
            foreach ($blockedBlogs as $blogObj) {
                foreach ($this->blogs as $blog) {
                    $blogObj->removeFollow($blog->ID);
                    $blog->removeFollow($blogObj->ID);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function block($userID) {
        $this->blockedUsers[] = $userID;
        $this->blockedUsers = array_unique($this->blockedUsers);
        $values = array($this->ID, $this->database->php_to_postgres($this->blockedUsers));
        $result = $this->database->db_update("UPDATE users SET blocked_users = $2 WHERE id = $1", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function resetPassword($input) {
        $password = password_hash($input, PASSWORD_DEFAULT);
        $values = array($this->ID, $password);
        $res = $this->database->db_update("UPDATE users SET password = $2 WHERE id = $1", $values);
        if ($res) {
            $this->markVerified();
            return true;
        } else {
            return false;
        }
    }

    public function requestPasswordReset() {
        $randomString = WFUtils::generateRandomString();
        $emailMessage = new EmailMessage();
        $emailMessage->disregardPrefs = true;
        $this->database->db_update("UPDATE users SET verify_key = $1 WHERE id = $2", array($randomString, $this->ID));
        $emailMessage->createMessage('password', array('address' => $this->email, 'randStr' => $randomString));
    }

    public function getByEmail(string $email) {
        $values = array($email);
        $result = $this->database->db_select("SELECT * FROM users WHERE lower(email) = lower($1)", $values);
        if ($result) {
            $row = $result[0];
            $this->ID = $row['id'];
            $this->theme = $row['dashboard_theme'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->mainBlog = $row['main_blog'];
            $this->accountType = $row['account_type'];
            $this->verified = $row['verified'];
            $this->tagBlacklist = $this->database->postgres_to_php($row['tag_blacklist']);
            $this->registeredAt = new DateTime($row['registered_at']);
            $this->lastVisit = new DateTime($row['last_visit']);
            $this->verifyKey = $row['verify_key'];

            $this->UUID = $row['uuid'];
            if ($row['date_of_birth'] != null) {
                $this->DOB = new DateTime($row['date_of_birth']);
            } else {
                $this->DOB = null;
            }
            #$this->sessionKey = $row['session_key'];
            $this->lastIP = $row['last_ip'];
            if ($row['blocked_users'] != '{}' && $row['blocked_users'] != null) {
                $this->blockedUsers = $this->database->postgres_to_php($row['blocked_users']);
            } else {
                $this->blockedUsers = array();
            }            
            if ($row['inbox_last_read'] != null) {
                $this->lastInboxTime = $row['inbox_last_read'];
            }
            $this->secretKey = $row['secret_key'];
            $this->timezone = $row['timezone'];
            $this->modLevel = $row['mod_level'];
            $this->getAllowedBadges($this->database->postgres_to_php($row['badges_allowed']));
            $this->getAllowedThemes($this->database->postgres_to_php($row['themes_allowed']));
            $this->subscriptionTier = $row['subscription_tier'];
            $this->pronouns = $row['pronouns'];
            if ($row['customer_id'] != null) {
                $this->stripeCustomerID = $row['customer_id'];
            }          
            if ($row['settings'] == '' || $row['settings'] == null) {
                // Nothing
            } else {
                $this->settings = json_decode($row['settings'], true); // True here gives us an associative array
            }
            $this->subscriptionEnds = new DateTime($row['subscription_ends']);
            $this->restrictions = $this->database->postgres_to_php($row['restrictions']);
            $this->userFlags = $this->database->postgres_to_php($row['flags']);
            $this->getUserBlogIDs();

            $this->getUserBlogs();
            $this->getUserGroupBlogs();

            return $this->ID;
        } else {

            return false;
        }
    }

    public function updatePronoun($option) {
        $values = array($this->ID, $option);
        $this->database->db_update("UPDATE users SET pronouns = $2 where id = $1", $values);
    }

    public function updateMissing($array) {
        /** 
         * Takes an array of options and does stuff accordingly. 
         */
        if (isset($array['birthday'])) {
            $dateObj = new DateTime($array['birthday']);
            $values = array($dateObj->format('Y-m-d'), $this->userID);
            $updateRes = $this->database->db_update("UPDATE users SET date_of_birth = $1 where id = $2", $values);
        }
        if (isset($array['timezone'])) {
            $values = array($array['timezone'], $this->userID);
            $updateRes = $this->database->db_update("UPDATE users SET timezone = $1 where id = $2", $values);
        }
    }

    public function updateVisit() {
        $values = array($_SERVER['REMOTE_ADDR'], $this->ID);
        $res = $this->database->db_update("UPDATE users SET last_ip = $1, last_visit = NOW() WHERE id = $2", $values);
    }

    public function register(string $blogName, string $password, string $emailAddress, string $birthday, string $invite = 'None') {
        /** Assumes all verification has been done at the API level. */
        $dateObj = new DateTime($birthday);
        $dateString = $dateObj->format('Y-m-d');
        $blog = new Blog();
        $blog->blogName = WFUtils::urlFixer($blogName);
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
        $randomString = WFUtils::generateRandomString();
        // TODO: EMAIL 
        $uuid = WFUtils::generateFingerprint();
        $accVals = array($emailAddress, $passwordHashed, $randomString, $dateString, $uuid, json_encode($this->settings), false);
        $accountResult = $this->database->db_insert("INSERT INTO users (email, password, verify_key, date_of_birth, uuid, settings, registered_at, inbox_last_read, verified, account_type, tag_blacklist, dashboard_theme) VALUES ($1, $2, $3, $4, $5, $6, NOW(), NOW(), $7, 'user', '{}', 1)", $accVals);
        if ($accountResult) {
            // Registering worked
            $this->ID = $accountResult;
            $this->updateSettings();
            $blog->ownerID = $accountResult;
            if ($blog->createBlog()) {
                $blogID = $blog->ID;
                $this->mainBlog = $blogID;
                $vals = array($blogID, $accountResult);
                $updateUser = $this->database->db_update("UPDATE users SET main_blog = $1 WHERE id = $2", $vals);
                if ($updateUser) {
                    $emailMessage = new EmailMessage();
                    $emailMessage->disregardPrefs = true;
                    $emailMessage->createMessage('new_account', array('address' => $emailAddress, 'randStr' => $randomString));
                    $blog->addFollow(1);
                    if ($invite != 'None') {
                        $invitingBlog = $this->retrieveInvite($invite);
                        if ($invitingBlog != false) {
                            $inVals = array($invite);
                            $this->database->db_update("UPDATE invites SET uses = uses + 1 WHERE code = $1", $inVals);
                            if ($invitingBlog != 'staff') {
                                $blog->addFollow($invitingBlog);
                                $invitingUser = new User($blog->ownerID);
                                $invitingUser->inviteBadgeCalc();
                            }
                        }
                        return true;
                    } else {
                        return true;
                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function login(string $emailAddress, string $password) {
        $values = array($emailAddress);
        $temp = $this->database->db_select("SELECT * FROM users WHERE lower(email) = lower($1)", $values);
        if (!$temp) {
            return false;
        } else {
            if (password_verify($password, $temp[0]['password'])) {
                // Successful login.
                $sessionObj = new Session();
                $sessionObj->getSession($_COOKIE['waterfall']);
                if ($sessionObj->sessionLogin($temp[0]['id'], $temp[0]['main_blog']) !== false) {
                    return $sessionObj->sessionID;
                } else {
                    return 0;
                }
            } else {
                return false;
            }
        }
    }
    
    public function confirmPassword($password) {
        return password_verify($password, $this->password);
    }

    public function banUser(string $reason = '') {
        $this->accountType = 'banned';
        $values = array($this->ID);
        $res = $this->database->db_update("UPDATE users SET account_type = 'banned' WHERE id = $1", $values);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
  
    public function addBadge(int $badgeID, string $message = '') {
        $values = array($this->ID);
        $userRow = $this->database->db_select("SELECT * FROM users WHERE id = $1", $values);
        if ($userRow[0]['badges_allowed'] == '' || empty($userRow[0]['badges_allowed'])) {
            $badges = array();
        } else {
            $badges = $this->database->postgres_to_php($userRow[0]['badges_allowed']);
        }
        $badges[] = $badgeID;
        $badges = array_unique($badges);
        $badges = sort($badges);
        $this->badgesAllowed = $badges;

        $values = array($this->database->php_to_postgres($badges), $this->ID);
        $result = $this->database->db_update("UPDATE users SET badges_allowed = $1 WHERE id = $2", $values);
        if ($message != '') {
            $messageObject = new Message();
            $messageObject->sender = -10;
            $messageObject->content = $message;
            $messageObject->recipient = $this->mainBlog;
            $messageObject->answerable = false;
            $messageObject->saveToDatabase();
        }
        return true;
    }

    public function removeBadge(int $badgeID, string $message = '') {
        $values = array($this->ID);
        $userRow = $this->database->db_select("SELECT * FROM users WHERE id = $1", $values);
        if ($userRow[0]['badges_allowed'] == '' || empty($userRow[0]['badges_allowed'])) {
            return true;
        } else {
            $badges = $this->database->postgres_to_php($userRow[0]['badges_allowed']);
            $key = array_search($badgeID, $badges);
            if (false !== $key) {
                unset($badges[$key]);
            }
            $badges = sort($badges);
            $this->badgesAllowed = $badges;
            $values = array($this->database->php_to_postgres($badges), $this->ID);
            $result = $this->database->db_update("UPDATE users SET badges_allowed = $1 WHERE id = $2", $values);
            if ($message != '') {
                $messageObject = new Message();
                $messageObject->sender = -10;
                $messageObject->content = $message;
                $messageObject->recipient = $this->mainBlog;
                $messageObject->answerable = false;
                $messageObject->saveToDatabase();
            }
        }
        return true;
    }

    public function getAllowedThemes($fromDB) {
        $vals = array(true);
        $defaultRes = $this->database->db_select("SELECT * FROM themes where default_theme = $1", $vals);
        if ($defaultRes) {
            foreach ($defaultRes as $res) {
                $this->themesAllowed[] = $res['id'];
            }
            foreach ($fromDB as $res) {
                $this->themesAllowed[] = $res;
            }
            $this->themesAllowed = array_unique($this->themesAllowed);
            $this->themesAllowed = array_filter($this->themesAllowed);
        }
    }

    public function getAllowedBadges($fromDB) {
        $vals = array(true);
        $defaultRes = $this->database->db_select("SELECT * FROM badges where default_badge = $1", $vals);
        if ($defaultRes) {
            foreach ($defaultRes as $res) {
                $this->badgesAllowed[] = $res['id'];
            }

            foreach ($fromDB as $res) {
                $this->badgesAllowed[] = $res;
            }
            $this->badgesAllowed = array_unique($this->badgesAllowed);
            $this->badgesAllowed = array_filter($this->badgesAllowed);
        }
    }
  
    public function addTheme(int $themeID) {
        $values = array($this->ID);
        $userRow = $this->database->db_select("SELECT * FROM users WHERE id = $1", $values);
        $themes = $this->database->postgres_to_php($userRow[0]['themes_allowed']);
        $themes[] = $themeID;
        $themes = array_unique($themes);
        $themes = sort($themes);
        $this->themesAllowed = $themes;

        $values = array($this->database->php_to_postgres($themes), $this->ID);
        $result = $this->database->db_update("UPDATE users SET themes_allowed = $1 WHERE id = $2", $values);
    }

    public function getUserUploadLimits(string $type) {
        // Returns in bytes, since that's how PHP and JavaScript's size checkers do it
        $base = array('image' => 10485760, 'video' => 104857600, 'audio' => 10485760); 
        return $base[$type];
    }

    public function setInboxTime() {
        $values = array($this->ID);
        $result = $this->database->db_update("UPDATE users SET inbox_last_read = NOW() WHERE id = $1", $values);
    }

    public function updateIP($IP) {
        $values = array($IP, $this->ID);
        $result = $this->database->db_update("UPDATE users SET last_ip = $1 WHERE id = $2", $values);
        $this->lastIP = $IP;
    }

    public function calculateAge() {
        if ($this->DOB == null || $this->DOB == '') {
            return 12; // Yeah yeah I know
        } else {
            $now = new DateTime();
            $age = $now->diff($this->DOB);
            return $age->y;
        }
    }
      
    public function getUserBlogs() {
        /** Returns an array of rows for the user's blog.
         *
        * @param userID The user's ID.
        * @return results An array of SQL rows.
        */
        $values = array($this->ID);
        $results = $this->database->db_select("SELECT * FROM blogs WHERE owner_id = $1", $values);
        foreach ($results as $blog) {
            $this->blogs[] = new Blog(intval($blog['id']));
        }
    }

    public function getUserGroupBlogs() {
        /** Returns an array of rows for the user's blog.
         *
        * @param userID The user's ID.
        * @return results An array of SQL rows.
        */
        $values = array($this->ID);
        $results = $this->database->db_select("SELECT * FROM blog_members WHERE user_id = $1 AND confirmed = 't'", $values);
        if ($results) {
            foreach ($results as $blog) {
                $this->groupBlogs[] = new Blog(intval($blog['blog_id']));
            }
        }
    }

    public function getUserGroupInvites() {
        /** Returns a list of blogs the user has been invited to, but hasn't accepted yet. 
         *
        * @param userID The user's ID.
        * @return results An array of SQL rows.
        */
        $values = array($this->ID);
        $results = $this->database->db_select("SELECT * FROM blog_members WHERE user_id = $1 AND confirmed = 'f'", $values);
        $ret = array();
        if ($results) {
            foreach ($results as $blog) {
                $ret[] = array($blog['blog_id'], $blog['id']);
            }
            return $ret;
        } else {
            return false;
        }
    }

    public function getUserBlogIDs() {
        $values = array($this->ID);
        $results = $this->database->db_select("SELECT * FROM blogs WHERE owner_id = $1", $values);
        $result = [];
        foreach ($results as &$blog) {
            $this->blogIDs[] = $blog['id'];
        }
    }

    public function updateBirthday($date) {
        $values = array($this->ID, $date);
        $result = $this->database->db_update("UPDATE users SET date_of_birth = $2 WHERE id = $1", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function getUserGroupBlogIDs() {
        $values = array($this->ID);
        $results = $this->database->db_select("SELECT * FROM blog_members WHERE user_id = $1 AND confirmed = 't'", $values);
        $result = [];
        foreach ($results as &$blog) {
            $this->groupBlogIDs = $blog['blog_id'];
        }
    }

    public function getUserBlogCount() {
        return count($this->blogs);
    }

    public function newDOB($dateObj) {
        $dString = $dateObj->format("Y-m-d H:i:s.u");
        $values = array($dString, $this->ID);
        $q = $this->database->db_update("UPDATE users SET date_of_birth = $1 WHERE id = $2", $values);
    }

    public function generateNewSessionKey() {
        $key = WFUtils::generateFingerprint();
        $values = array($key, $this->ID);
        $query = $this->database->db_update("UPDATE users SET session_key = $1 WHERE id = $2", $values);
        return $key;
    }

    public function updateEmail($email) {
        if (WFUtils::emailCheck($email)) {
            // Can do it
            $this->email = $email;
            $values = array($this->email, $this->ID);
            $result = $this->database->db_update("UPDATE users SET email = $1, verified = 'f' WHERE id = $2", $values);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function updatePassword($password) {
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
        $values = array($passwordHashed, $this->ID);
        $result = $this->database->db_update("UPDATE users SET password = $1 WHERE id = $2", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function setTheme($themeID) {
        $values = array($this->ID, $themeID);
        $result = $this->database->db_update("UPDATE users SET dashboard_theme = $2 WHERE id = $1", $values);
    }

    public function updateSettings() {
        if ($this->ID != null) {
            $string = json_encode($this->settings);
            $values = array($string, $this->ID);
            $result = $this->database->db_update("UPDATE users SET settings = $1 WHERE id = $2", $values);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } 
    }

    public function canMakeBlog() {
        return true;

    }

    public function retrieveInvite($code) {
        $values = array($code);
        $result = $this->database->db_select("SELECT * FROM invites WHERE code = $1", $values);
        if ($result) {
            return $result[0]['for_blog'];
        } else {
            return false;
        }
    }

    public function inviteBadgeCalc() {
        $blogArray = implode(',', $this->blogIDs);
        $values = array();
        $query = $this->database->db_select("SELECT SUM(uses) AS count FROM invites WHERE for_blog IN (".$blogArray.")", $values);
        $result = $query[0]['count'];
        if ($result >= 1) {
            $this->addBadge(13);
        }
        if ($result >= 10) {
            $this->addBadge(14);
        }
    }

    public function getUnreadChatMessageCount() {
      return 0; // Not implemented yet
    }

    public function getUnreadInboxCount() {
        $blogArray = implode(',', $this->blogIDs);
        $values = array($this->lastInboxTime);
        $result = $this->database->db_count("SELECT * FROM messages WHERE recipient IN ($blogArray) AND answered = 'f' AND deleted_inbox = 'f'  AND timestamp > $1 ORDER BY id DESC", $values);
        return $result;
    }

    public function deleteAccount() {
        foreach ($this->blogs as $blog) {
            $blog->deleteBlog();
        }
        $values = array($this->ID);
        $blogMems = $this->database->db_delete("DELETE FROM blog_members WHERE user_id = $1", $values);
        $votes = $this->database->db_delete("DELETE FROM votes WHERE from_user = $1", $values);
        $result = $this->database->db_delete("DELETE FROM users WHERE id = $1", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function changePassword($password) {
        $values = array(password_hash($password, PASSWORD_DEFAULT), $this->ID);
        $result = $this->database->db_update("UPDATE users SET password = $1 WHERE id = $2", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function updateTagBlacklist() {
        $blacklist = array_unique($this->tagBlacklist);
        $blacklist = array_filter($blacklist);
        $values = array($this->database->php_to_postgres($blacklist), $this->ID);
        $update = $this->database->db_update("UPDATE users set tag_blacklist = $1 where id = $2", $values);
    }

    public function getTagBlacklistString() {
        $tagBlacklist = implode(', ', $this->tagBlacklist);
        $tagBlacklist = str_replace("\'", "'", $tagBlacklist);
        $tagBlacklist = str_replace('\"', '"', $tagBlacklist);
        return $tagBlacklist;
    }
}