<?php

class Tag {

    /** 
     * Tag class. Contains everything needed for tags.
     */
    private $database;
    public $ID;
    public $string;
    public $lowercased;
    public $isDNR;

    public function __construct($input, $type) {
        /**
         * Constructor function. Depending on the type specified, it takes different paths. 
         * 
         * Additionally, if the tag doesn't exist, this function creates it.
         * 
         * @param input The tag string or ID. 
         * @param type The type of input. "text" denotes a string, "id" denotes an ID.
         */
        $this->database = Postgres::getInstance();
        if ($type == 'id') {
            $input = intval($input);
            $values = array($input);
            $result = $this->database->db_select("SELECT * FROM tags WHERE id = $1", $values);
            if ($result) {
                $res = $result[0];
                $this->ID = $res['id'];
                $this->string = $res['tag'];
                $this->lowercased = $res['lowercased'];

            }
        } else if ($type == 'text') {
            $this->string = $input;
            $values = array($this->string);
            $result = $this->database->db_select("SELECT * FROM tags WHERE tag = $1", $values);
            if (!$result) {
                $values = array($input, $input);
                $result = $this->database->db_insert("INSERT INTO tags (tag, lowercased) VALUES ($1, LOWER($2))", $values);
                if ($result) {
                    $values = array($result);
                    $result = $this->database->db_select("SELECT * FROM tags WHERE id = $1", $values);
                    if ($result) {
                        $res = $result[0];
                        $this->ID = $res['id'];
                        $this->string = $res['tag'];
                        $this->lowercased = $res['lowercased'];
                    }
                }
            } else {
                $res = $result[0];
                $this->ID = $res['id'];
                $this->string = $res['tag'];
                $this->lowercased = $res['lowercased'];
                
            }
        }
    }

    public function isDNR() {
        /** 
         * Checks a tag's DNR/DNI status.
         */
        if ($this->lowercased == 'dni' || $this->lowercased == 'do not interact') {
            $this->isDNR = 'dni';
            return 'dni';
        }
        if ($this->lowercased == 'dnr' || $this->lowercased == 'do not reblog') {
            $this->isDNR = 'dnr';
            return 'dnr';
        }
        return false;
    }
}
