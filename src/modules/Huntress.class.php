<?php 

class Huntress {



    public static function checkIPBan($ipAddress) {
        $database = Postgres::getInstance();
        $values = array($_SERVER['REMOTE_ADDR']);
        $result = $database->db_select("SELECT * FROM ip_bans WHERE $1 <<= ip_address", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}