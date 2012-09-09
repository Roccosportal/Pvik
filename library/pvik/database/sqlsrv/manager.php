<?php

namespace Pvik\Database\SQLSRV;

use Pvik\Core\Log;

/**
 * Runs sql statements.
 * Uses the function sqlsrv_ for the database.
 */
class Manager extends \Pvik\Database\SQL\Manager {

    /**
     * Contains the connection to the database.
     * @var mixed 
     */
    protected $Connection;

    /**
     * Connect to the database
     */
    protected function __construct() {
        $SQLSRV = \Pvik\Core\Config::$Config['SQLSRV'];

        $ConnectionOptions = array('Database' => $SQLSRV['Database'], 'UID' => $SQLSRV['Username'], 'PWD' => $SQLSRV['Password'], "CharacterSet" => "UTF-8");
        $this->Connection = sqlsrv_connect($SQLSRV['Server'], $ConnectionOptions);
        if ($this->Connection === false) {
            throw new \Exception('Database error: ' . $this->FormatErrors(sqlsrv_errors()));
        }
    }

    /**
     * Returns the current connection
     * @return mixed 
     */
    public function GetConnection() {
        return $this->Connection;
    }

    /**
     * Executes a statement.
     * @param string $QueryString
     * @return mixed 
     */
    public function ExecuteQueryString($QueryString) {
        $Connection = $this->GetConnection();
        Log::WriteLine('Executing querystring: ' . $QueryString);
        $Result = sqlsrv_query($Connection, $QueryString);
        ;
        if ($Result === false) {
            throw new Exception('Could not execute following statement: ' . $QueryString . "\n" . 'SQLSRVError: ' . $this->FormatErrors(sqlsrv_errors()));
        }
        return $Result;
    }

    /**
     *  Returns the last inserted id
     * @return mixed 
     */
    public function GetLastInsertedId() {
        $ID = 0;
        $Result = self::ExecuteQueryString("SELECT @@identity AS id");
        if ($Row = sqlsrv_fetch_array($Result)) {
            $ID = $Row["id"];
        }
        return $ID;
    }

    /**
     *  Escapes a string.
     * @param string $String
     * @return string
     */
    public function EscapeString($String) {
        return str_replace("'", "''", $String);
    }

    /**
     *  Fetches an associative array from a database result
     * @param mixed $Result
     * @return array
     */
    public function FetchAssoc($Result) {
        return sqlsrv_fetch_array($Result, SQLSRV_FETCH_ASSOC);
    }

    /**
     * Formats errors
     * @param arrray  $Errors
     * @return string 
     */
    protected function FormatErrors(array $Errors) {
        $ErrorMessage = "Error information: \n";
        foreach ($Errors as $Error) {
            $ErrorMessage .= "SQLSTATE: " . $Error['SQLSTATE'] . "\n";
            $ErrorMessage .= "Code: " . $Error['code'] . "\n";
            $ErrorMessage .= "Message: " . $Error['message'] . "\n";
        }
        return $ErrorMessage;
    }

}