<?php

class WFi18n extends \i18n {
    public function getUserLangs() {
        /**
         * Get the list of languages that can be used.
         *
         * We override this from the base i18n class to change the order in
         * which languages are chosen, and the order is:
         *
         * 1. Forced language
         * 2. Language in $_GET['lang']
         * 3. Language in $_COOKIE['lang']
         * 4: Language in $_SESSION['lang']
         * 5. HTTP_ACCEPT_LANGUAGE
         * 6. Fallback language
         *
         * Waterfall does not use PHP sessions, and as such $_SESSION['lang']
         * will never be used here, but it's included for future use (and for
         * if this override gets used elsewhere).
         */

        $userLangs = array();

        // Highest priority: forced language
        if (!is_null($this->forcedLang)) {
            $userLangs[] = $this->forcedLang;
        }

        // Second highest priority: GET parameter
        if (isset($_GET['lang']) && is_string($_GET['lang'])) {
            $userLangs[] = $_GET['lang'];
        }

        // Third highest priority: COOKIE
        if (isset($_COOKIE['lang']) && is_string($_COOKIE['lang'])) {
            $userLangs[] = $_COOKIE['lang'];
        }

        // Fourth highest priority: SESSION parameter
        if (isset($_SESSION['lang']) && is_string($_SESSION['lang'])) {
            $userLangs[] = $_SESSION['lang'];
        }

        // Fifth highest priority: HTTP_ACCEPT_LANGUAGE
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $part) {
                $userLangs[] = strtolower(substr($part, 0, 2));
            }
        }

        // Lowest priority: fallback
        $userLangs[] = $this->fallbackLang;

        // Remove duplicates and illegal values
        $finalUserLangs = array();
        foreach (array_unique($userLangs) as $key => $value) {
            if (preg_match('/^[a-zA-z0-9_-]{2,}$/', $value)) {
                $finalUserLangs[] = $value;
            }
        }

        return $finalUserLangs;
    }
}
