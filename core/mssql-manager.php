<?php
Core::Depends('~/core/mysql-manager.php');
/**
 * Uses the function mssql_ for the database.
 */
class MSSQLManager extends MySQLManager {

    /**
     * Contains the instance of the MSSQLManager 
     * @var MSSQLManager 
     */
    protected static $Instance;

    /**
     * Connect to the database
     */
    protected function __construct() {
        $MSSQL = Core::$Config['MSSQL'];
        ini_set('mssql.charset', 'UTF-8');
        $this->Connection = mssql_connect($MSSQL['Server'], $MSSQL['Username'], $MSSQL['Password']);
        if (!$this->Connection) {
            throw new Exception('Database error: ' . mssql_get_last_message());
        } else {
            if (!mssql_select_db($MSSQL['Database'])) {
                throw new Exception('Database error: ' . mssql_get_last_message());
            }
        }
    }

    /**
     * Creates a instance a automatically connects.
     */
    protected static function Connect() {
        if (self::$Instance == null) {
            self::$Instance = new MSSQLManager();
        }
    }

    /**
     * Executes a statement.
     * @param string $QueryString
     * @return mixed 
     */
    public static function ExecuteQueryString($QueryString) {
        self::Connect();
        Log::WriteLine('Executing querystring: ' . $QueryString);
        $Result = mssql_query($QueryString);
        if (!$Result) {
            throw new Exception('Could not execute following statement: ' . $QueryString . "\n" . 'MSSQLError: ' . mssql_get_last_message());
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
        return self::mssql_insert_id();
    }

    /**
     * Escape parameters.
     * @param array $Parameters
     * @return array 
     */
    public static function ConvertParameters(array $Parameters) {
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
        while ($Data = mssql_fetch_assoc($Result)) {
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
    protected static function mssql_insert_id() {
        $ID = 0;
        $Result = mssql_query("SELECT @@identity AS id");
        if ($Row = mssql_fetch_array($Result, MSSQL_ASSOC)) {
            $ID = $Row["id"];
        }
        return $ID;
    }

}

?>