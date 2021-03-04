<?php

/**
 * @class Database
 * @brief A class for handling Postgres transactions.
 *
 * Handles transactions to and from a Postgres database server.
 * @author Benjamin Clarke
*/

class Postgres {

    protected $connection;
    private static $instance;

    public function __construct() {
    /**
     * @brief Initialise the database with the specified settings.
     *
     * Create a database connection.
     * @param $credentials array Array of credentials to use. 
    */
        $connStr = "host=".$_ENV['DB_HOST']." dbname=".$_ENV['DB_NAME']." user=".$_ENV['DB_USER']." password=".$_ENV['DB_PASS'];
        $this->connection = pg_connect($connStr);
        //pg_set_client_encoding($this->connection, 'UNICODE');
        // TODO - Error handling.
    }

    private function logErroredQuery($errorString) {
        if ($_ENV['ENVIRONMENT'] == 'dev') {
            echo $errorString;
        }
    }

    private function db_query($query, $params) {
        /**
        * @brief Generic query handler.
        *
        * Performs a query as specified. Returns a raw result resource.
        *
        * @param $query string The query to run.
        * @return $result result A result resource.
        */
        foreach ($params as &$value) {
            if ($value === true) {
                $value = 't';
            } else if ($value === false) {
                $value = 'f';
            }
        }
        $result = pg_query_params($this->connection, $query, $params);
        return $result;
    }

    public function db_escape($value) {
        $result = pg_escape_literal($this->connection, $value);
        return $result;

    }

    public function db_count($query, $params) {
        $result = $this->db_query($query, $params);
        return pg_num_rows($result);
    }

    public function db_select($query, $params) {
        /**
        * @brief Generic query handler to select items.
        *
        * Performs a query as specified. Returns all rows as an associative array. Arrays in SQL will be returned as JSON!
        *
        * @param $query string The query to run.
        * @return $rows array A multidimensional array of returned results. Where only one item is returned,
        * it will always be at position 0 in an array to simplify usage. Null on failure, false on no rows.
        */
        $result = $this->db_query($query, $params);
        if (!$result) {
            $errorString = "ERROR MESSAGE: ".pg_result_error($result)."\r\nQUERY: ".$query."\r\n PARAMETERS: ".implode(', ', $params)."\r\n";
            $this->logErroredQuery($errorString);
            return null;
        }
        if (pg_num_rows($result) != 0) {
            while ($row = pg_fetch_assoc($result)) {
                foreach ($row as $key => &$value){
                    $type = pg_field_type($result,pg_field_num($result, $key));
                    if ($type == 'bool'){
                        $value = ($value == 't');
                    }
                }
                $rows[] = $row;
            }
            
            return $rows;
        } else {
            return false;
        }
    }

    public function db_insert($query, $params) {
        /**
        * @brief Inserts a row into the database.
        *
        * Performs a query as specified. Returns the ID of the inserted row on success, or null if not..
        *
        * @param $query string The query to run.
        * @return $rowID int The ID of the inserted row, or false on failure.
        */
        $query = rtrim($query);
        $query = $query.' RETURNING id'; // We assume the PK is always ID
        $result = $this->db_query($query, $params);
        if (!$result) {
            $errorString = "ERROR MESSAGE: ".pg_result_error($result)."\r\nQUERY: ".$query."\r\n PARAMETERS: ".implode(', ', $params)."\r\n";
            $this->logErroredQuery($errorString);
            return null;
        }
        // A result resoure has been returned. To get the ID, we need to...
        $row = pg_fetch_assoc($result);
        $rowID = $row['id'];
        return $rowID;

    }

    public function db_update($query, $params) {
        /**
        * @brief Updates a row in the database.
        *
        * Performs a query as specified. Returns true on success.
        *
        * @param $query string The query to run.
        * @return $blank bool True on success, false on failure.
        */
        $result = $this->db_query($query, $params);
        if (!$result) {
            $errorString = "ERROR MESSAGE: ".pg_result_error($result)."\r\nQUERY: ".$query."\r\n PARAMETERS: ".implode(', ', $params)."\r\n";
            $this->logErroredQuery($errorString);
            return null;
        } else {
            return true;
        }
    }

    public function db_delete($query, $params) {
        /**
        * @brief Deletes a row from the database.
        *
        * Performs a query as specified. Returns true on success, false otherwise.
        *
        * @param $query string The query to run.
        * @return $blank bool True on success, false otherwise.
        */
        $result = $this->db_query($query, $params);
        if (!$result) {
            $errorString = "ERROR MESSAGE: ".pg_result_error($result)."\r\nQUERY: ".$query."\r\n PARAMETERS: ".implode(', ', $params)."\r\n";
            $this->logErroredQuery($errorString);
        return null;
        } else {
            return true;
        }
    }

    public function postgres_to_php($array) {
        $pgString = trim($array,"{}");
        $result = explode(",",$pgString);
        foreach ($result as &$res) {
            $res = trim($res, '"');
        }
        return $result;
    }

    public function php_to_postgres($array) {
        return "{".implode(',',$array)."}";
    }

    public function __destruct() {
        pg_close($this->connection);
    }

    public static function getInstance() {
        if (self::$instance == null) {
          self::$instance = new Postgres();
        }
        return self::$instance;
    }
}
