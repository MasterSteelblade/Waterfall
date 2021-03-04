<?php

class Serial {
    private $database;
    private $ID;
    private $entitlements;
    public $entitlementPrintout = '<ul>';

    public function __construct() {
        $this->database = Postgres::getInstance();
    }

    public function setEntitlements($entitlements) {
        $this->entitlements = $entitlements;
    }

    public function getByKey($key) {
        $values = array($key);
        $result = $this->database->db_select("SELECT * FROM serials WHERE key = $1 AND `valid` = 't'", $values);
        if ($result) {
            $this->ID = $result[0]['id'];
            $this->entitlements = $this->database->postgres_to_php($reslt[0]['entitlements']);
            $this->key = $result[0]['key'];
            return true;

        } else {
            return false;
        }
    }

    public function getEntitlementRows() {
        $entitlementString = implode(',', $this->entitlements);
        $values = array();
        $entitlements = $this->database->db_select("SELECT * FROM entitlements WHERE id IN (".$entitlementString.")", $values);
        if ($entitlements) {
            return $entitlements;
        } else {
            return false;
        }
    }

    public function generate($segments = 4, $length = 5) {
        $tokens = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No ambiguous characters - O, 0, l, I, 1
        $string = '';
        for ($i = 0; $i < $segments; $i++) {
            $segment = '';
            for ($j = 0; $j < $length; $j++) {
                $segment .= $tokens[rand(0, strlen($tokens)-1)];
            }
            $string .= $segment;
            if ($i < ($segments - 1)) {
                $string .= '-';
            }
        }
        $entitlements = $this->database->php_to_postgres($this->entitlement);
        $values = array($string, $entitlements);
        $result = $this->database->db_insert("INSERT INTO serials (key, entitlements, valid) VALUES ($1, $2, 1)", $values);
        if ($result) {
            return $string;
        }
    }

    private function grantPackEntitlements($entitlements, $user) {
        foreach ($entitlements as $ent) {
            $this->entitlementPrintout = $this->entitlementPrintout.'<li>'.$ent['description'].'</li>';
            if ($ent['type'] == 'badge') {
                $user->addBadge(intval($ent['badgeID']));
            } elseif ($ent['type'] == 'theme') {
                $user->addTheme(intval($ent['themeID']));
            } elseif ($ent['type'] == 'subscription') {
                $user->addSubscription(intval($ent['subscriptionMonths']), $subscriptionType);
            }
        }
    }

    public function grantEntitlements($user) {
        $entitlements = $this->getEntitlementRows();
        if ($entitlements) {
            $user = new User(intval($this->user));
            // Safe to assume the user activating the key is who it's meant for
            foreach ($entitlements as $ent) {
                if ($ent['isPack']) { // Do this first, because if it's a pack, thee'll be no more after it.
                    $packContents = $ent['packContents'];
                    $values = array();
                    $entitlements = db_select("SELECT * FROM entitlements WHERE id IN (".$packContents.")", $values);
                    $this->grantPackEntitlements($entitlements, $user);
                } elseif ($ent['type'] == 'badge') {
                    $user->addBadge(intval($ent['badgeID']));
                } elseif ($ent['type'] == 'theme') {
                    $user->addTheme(intval($ent['themeID']));
                } elseif ($ent['type'] == 'subscription') {
                    $user->addSubscription(intval($ent['subscriptionMonths']), $subscriptionType);
                }
            }
            $values = array($user->ID, $this->ID);
            $upd = $this->database->db_update("UPDATE serials SET valid = 0, used_by = $1 WHERE id = $2", $values);
            return true;
        } else {
            return false;
        }
    }
}
