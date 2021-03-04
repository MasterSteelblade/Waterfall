<?php

/**
 * Kestrel Core Module.
 *
 * This should be extended per site.
 *
 */

class KestrelEvent {

    const KESTREL_VERSION = 1;
    private $database;

    protected function __construct($userID = null, $activeBlog = null, $mod = 0) {
        $this->database = Postgres::getInstance();
        if ($mod == 1) {
            $this->mod = 1;
        } else {
            $this->mod = 0;
        }
        $this->userAgent = false;
        $this->localHour = false;
        $this->localMinute = false;
        $this->localSecond  = false;
        $this->idPageview = false;
        $this->kestrelVersion = self::KESTREL_VERSION;

        $this->urlReferrer = !empty($_SERVER['HTTP_REFERRER']) ? $_SERVER['HTTP_REFERRER'] : false;
        $this->pageURL = $this->getCurrentURL();
        $this->IP = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
        $this->acceptLanguage = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false;
        $this->userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;


        $this->userID = false;
        $this->randomUserID = false;
        $this->sessionID = false;
        $this->setID($userID);
        $this->session();

        $this->timestamp = time();

        $this->generateNewPageviewID();
    }

    private function setID($userID) {
        if (!isset($_COOKIE['visid'])) {
            $this->randomUserID = generateFingerprint();
            setcookie("visid", $this->randomUserID, time()+33955200,'/',$_ENV['COOKIE_URL'], true, true);
        } else {
            $this->randomUserID = $_COOKIE['visid'];
        }
        if (isset($userID) && $userID != null) {
            $this->userID = $userID;
        }
    }

    private function session() {

        if (!isset($_COOKIE['visess'])) {
            $this->sessionID = generateFingerprint();
            setcookie("visess", $this->sessionID, time()+1800,'/',$_ENV['COOKIE_URL'], true, true);
        } else {
            $this->sessionID = $_COOKIE['visess'];
            setcookie("visess", $this->sessionID, time()+1800,'/',$_ENV['COOKIE_URL'], true, true);

        }
    }

    public function setGenerationTime($time) {
        $this->generationTime = $timeMs;
        return $this;
    }

    private function generateNewPageviewID() {
        $this->idPageview = substr(md5(uniqid(rand(), true)), 0, 16);
    }

    protected static function getCurrentScriptName() {
        $url = '';
        if (!empty($_SERVER['PATH_INFO'])) {
            $url = $_SERVER['PATH_INFO'];
        } else {
            if (!empty($_SERVER['REQUEST_URI'])) {
                if (($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false) {
                    $url = substr($_SERVER['REQUEST_URI'], 0, $pos);
                } else {
                    $url = $_SERVER['REQUEST_URI'];
                }
            }
        }
        if (empty($url) && isset($_SERVER['SCRIPT_NAME'])) {
            $url = $_SERVER['SCRIPT_NAME'];
        } elseif (empty($url)) {
            $url = '/';
        }

        if (!empty($url) && $url[0] !== '/') {
            $url = '/' . $url;
        }

        return $url;
    }

    protected static function getCurrentScheme() {
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)) {
            return 'https';
        } else {
            return 'http';
        }
    }

    protected static function getCurrentHost() {
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        } else {
            return 'unknown';
        }
    }

    protected static function getCurrentQueryString() {
        $url = '';
        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }
        return $url;
    }

    protected static function getCurrentURL() {
        return self::getCurrentScheme().'://'.self::getCurrentHost().self::getCurrentScriptName().self::getCurrentQueryString();
    }

    public function store() {
        $json = json_encode(get_object_vars($this));
        $values = array($json);
        $query = $this->database->db_insert("INSERT INTO analytics (data) VALUES ($1)", $values);
    }

    private function spiderDetect() {
        // User lowercase string for comparison.
        $user_agent = strtolower($this->userAgent);

        // A list of some common words used only for bots and crawlers.
        $bot_identifiers = array(
        'bot',
        'slurp',
        'crawler',
        'spider',
        'curl',
        'facebook',
        'fetch',
        );

        // See if one of the identifiers is in the UA string.
        foreach ($bot_identifiers as $identifier) {
            if (strpos($user_agent, $identifier) !== FALSE) {
                return TRUE;
            }
        }

        return FALSE;
    }

    function __destruct() {
        if ($this->spiderDetect() == FALSE && $this->mod == 0) {
            $this->store();
        }
    }
}
