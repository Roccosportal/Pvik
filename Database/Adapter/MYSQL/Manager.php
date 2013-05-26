<?php

namespace Pvik\Database\Adapter\MYSQL;

use Pvik\Core\Log;

/**
 * Uses the  mysql_ functions for the database.
 * Runs sql statements.
 */
class Manager extends \Pvik\Database\SQL\Manager {

    /**
     * Contains the connection to the database.
     * @var mixed 
     */
    protected $connection;

    /**
     * Connect to the database
     */
    protected function __construct() {
        $mySQL = \Pvik\Core\Config::$config['Database'];
        $this->connection = mysql_connect($mySQL['Server'], $mySQL['Username'], $mySQL['Password']);
        if (!$this->connection) {
            throw new \Exception('Database error: ' . mysql_error());
        } else {
            mysql_set_charset('utf8', $this->connection);
            if (!mysql_select_db($mySQL['Database'])) {
                throw new \Exception('Database error: ' . mysql_error());
            }
        }
    }

    /**
     * Executes a statement.
     * @param string $queryString
     * @return mixed 
     */
    public function execute($queryString) {
        Log::writeLine('Executing querystring: ' . $queryString);
        $result = mysql_query($queryString);
        if (!$result) {
            throw new \Exception('Could not execute following statement: ' . $queryString . "\n" . 'MySQLError: ' . mysql_error());
        }
        return $result;
    }

    /**
     *  Returns the last inserted id
     * @return mixed 
     */
    public function getLastInsertedId() {
        return mysql_insert_id();
    }

    /**
     *  Escapes a string.
     * @param string $string
     * @return string
     */
    public function escapeString($string) {
        return mysql_escape_string($string);
    }

    /**
     *  Fetches an associative array from a database result
     * @param mixed $result
     * @return array
     */
    public function fetchAssoc($result) {
        return mysql_fetch_assoc($result);
    }

}