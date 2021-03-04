<?php 
/**
 * @class DatabaseManager
 * @brief A class for handling collections of multiple databases.
 *
 * Provides an interface for retrieving the database object to communicate with, and can create new ones. 
 * @author Benjamin Clarke
*/


class DatabaseManager {

    public $databases = array();
    public $dbTypes = array();

    private function __construct() {
    
    }

    public function getDatabase($type, $value = 0, $engine = 'postgres') {
    /**
     * @brief Creates/Returns a database object instance. 
     *
     * Creates or returns a given database instance based on the provided values.
     *
     * @param $type string What type of database is being searched.
     * @param $value int Optionally specified, the value of the type we're looking for to 
     *                      determine which database slice it's in. NOT CURRENTLY USED
     * @return $result Postgres The database object to use.
     * 
    */
    if ($engine == 'postgres') {
        if (array_key_exists($type, $this->postgres)) {
            return $this->databases[$type];
        } else {
            $credentials = $this->getCredentials($type);
            $this->databases[$type] = new Postgres();
        }
    }
        
    }

    public function getCredentials($database) {
        // Loads credentials from the INI
        $ini = parse_ini_file($file, true);
        $subsection = 'database:'.$database;
        $section = $ini[$subsection];
        $credentials = array();
        $credentials['database'] = $section['database'];
        $credentials['username'] = $section['username'];
        $credentials['password'] = $section['password'];
        $credentials['hostname'] = $section['hostname'];
        return $credentials;
    }

    public static function getInstance() {
        if (self::$instance == null) {
          self::$instance = new DatabaseCollector();
        }
        return self::$instance;
    }

}