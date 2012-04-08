<?php
Core::Depends('~/core/mysql-manager.php');
/**
 * Uses the function sqlsrv_ for the database.
 */
class SQLSRVManager extends MySQLManager {

    /**
     * Contains the instance of the SQLSRVManager 
     * @var SQLSRVManager 
     */
    protected static $Instance;

    /**
     * Connect to the database
     */
    protected function __construct() {
        $SQLSRV = Core::$Config['SQLSRV'];

        $ConnectionOptions = array('Database' => $SQLSRV['Database'], 'UID' => $SQLSRV['Username'], 'PWD' => $SQLSRV['Password'], "CharacterSet" => "UTF-8");
        $this->Connection = sqlsrv_connect($SQLSRV['Server'], $ConnectionOptions);
        if ($this->Connection === false) {
            throw new Exception('Database error: ' . self::FormatErrors(sqlsrv_errors()));
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
     * Creates a instance and returns the connection. 
     * @return type 
     */
    protected static function Connect() {
        if (self::$Instance == null) {
            self::$Instance = new SQLSRVManager();
        }
        return self::$Instance->GetConnection();
    }

    /**
     * Executes a statement.
     * @param string $QueryString
     * @return mixed 
     */
    public static function ExecuteQueryString($QueryString) {
        $Connection = self::Connect();
        Log::WriteLine('Executing querystring: ' . $QueryString);
        $Result = sqlsrv_query($Connection, $QueryString);
        ;
        if ($Result === false) {
            throw new Exception('Could not execute following statement: ' . $QueryString . "\n" . 'SQLSRVError: ' . self::FormatErrors(sqlsrv_errors()));
        }
        return $Result;
    }

    /**
     * Executes a insert statement
     * @param string $QueryString
     * @return mixed 
     */
    public static function Insert($QueryString) {
        self::ExecuteQueryString($QueryString);
        return self::sqlsrv_insert_id();
    }

    /**
     * Escape parameters.
     * @param array $Parameters
     * @return array 
     */
    public static function ConvertParameters(array $Paremeters) {
        $ConvertedParameters = array();
        foreach ($Parameters as $Parameter) {
            array_push($ConvertedParameters, self::mssql_escape_string($Parameter));
        }
        return $ConvertedParameters;
    }

    /**
     * Creates a ModelArray from a select statemet
     * @param ModelTable $ModelTable
     * @param string $QueryString
     * @param array $Parameters
     * @return ModelArray 
     */
    public static function FillList(ModelTable $ModelTable, $QueryString, array $Parameters) {
        $Result = SQLManager::SelectWithParameters($QueryString, $Parameters);
        $List = new ModelArray();
        $List->SetModelTable($ModelTable);
        while ($Data = sqlsrv_fetch_array($Result, SQLSRV_FETCH_ASSOC)) {
            $Classname = $ModelTable->GetModelClassName();
            $Model = new $Classname();
            $Model->Fill($Data);
            $List->append($Model);
        }
        return $List;
    }

    /**
     * Escapes a string.
     * @param string $String
     * @return string 
     */
    protected static function mssql_escape_string($String) {
        return str_replace("'", "''", $String);
    }

    /**
     * Returns the last inserted id.
     * @return mixed 
     */
    protected static function sqlsrv_insert_id() {
        $ID = 0;
        $Result = self::ExecuteQueryString("SELECT @@identity AS id");
        if ($Row = sqlsrv_fetch_array($Result)) {
            $ID = $Row["id"];
        }
        return $ID;
    }

    /**
     * Formats errors
     * @param arrray  $Errors
     * @return string 
     */
    protected static function FormatErrors(array $Errors) {
        $ErrorMessage = "Error information: \n";
        foreach ($Errors as $Error) {
            $ErrorMessage .= "SQLSTATE: " . $Error['SQLSTATE'] . "\n";
            $ErrorMessage .= "Code: " . $Error['code'] . "\n";
            $ErrorMessage .= "Message: " . $Error['message'] . "\n";
        }
        return $ErrorMessage;
    }

}

?>