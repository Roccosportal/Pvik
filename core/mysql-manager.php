<?php

/**
 * Uses the  mysql_ functions for the database.
 */
class MySQLManager {

    /**
     * Contains the instance of the MSSQLManager 
     * @var MSSQLManager 
     */
    protected static $Instance;

    /**
     * Contains the connection to the database.
     * @var mixed 
     */
    protected $Connection;

    /**
     * Connect to the database
     */
    protected function __construct() {
        $MySQL = Core::$Config['MySQL'];
        $this->Connection = mysql_connect($MySQL['Server'], $MySQL['Username'], $MySQL['Password']);
        if (!$this->Connection) {
            throw new Exception('Database error: ' . mysql_error());
        } else {
            mysql_set_charset('utf8', $this->Connection);
            if (!mysql_select_db($MySQL['Database'])) {
                throw new Exception('Database error: ' . mysql_error());
            }
        }
    }

    /**
     * Creates a instance a automatically connects.
     */
    protected static function Connect() {
        if (self::$Instance == null) {
            self::$Instance = new MySQLManager();
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
        $Result = mysql_query($QueryString);
        if (!$Result) {
            throw new Exception('Could not execute following statement: ' . $QueryString . "\n" . 'MySQLError: ' . mysql_error());
        }
        return $Result;
    }

    /**
     * Executes a select statement.
     * @param type $QueryString
     * @return mixed 
     */
    public static function Select($QueryString) {
        return self::ExecuteQueryString($QueryString);
    }

    /**
     * Executes a select statement with parameters.
     * @param string $QueryString
     * @param array $Parameters
     * @return mixed 
     */
    public static function SelectWithParameters($QueryString, array $Parameters) {
        $ConvertedParameters = self::ConvertParameters($Parameters);
        $QueryString = vsprintf($QueryString, $ConvertedParameters);
        return self::Select($QueryString);
    }

    /**
     * Executes a insert statement
     * @param string $QueryString
     * @return mixed 
     */
    public static function Insert($QueryString) {
        self::ExecuteQueryString($QueryString);
        return mysql_insert_id();
    }

    /**
     * Executes a insert statement with parameters.
     * @param string $QueryString
     * @param array $Parameters
     * @return mixed 
     */
    public static function InsertWithParameters($QueryString, array $Parameters) {
        $ConvertedParameters = self::ConvertParameters($Parameters);
        $QueryString = vsprintf($QueryString, $ConvertedParameters);
        return self::Insert($QueryString);
    }

    /**
     * Executes a update statement.
     * @param string $QueryString
     * @return mixed 
     */
    public static function Update($QueryString) {
        return self::ExecuteQueryString($QueryString);
    }

    /**
     * Executes a update statement with parameters.
     * @param string $QueryString
     * @param array $Parameters
     * @return type 
     */
    public static function UpdateWithParameters($QueryString, array $Parameters) {
        $ConvertedParameters = self::ConvertParameters($Parameters);
        $QueryString = vsprintf($QueryString, $ConvertedParameters);
        return self::Update($QueryString);
    }

    /**
     * Executes a delete statement.
     * @param string $QueryString
     * @return mixed 
     */
    public static function Delete($QueryString) {
        return self::ExecuteQueryString($QueryString);
    }

    /**
     * Executes a delete statement with parameters
     * @param type $QueryString
     * @param type $Parameters
     * @return type 
     */
    public static function DeleteWithParameters($QueryString, array $Parameters) {
        $ConvertedParameters = self::ConvertParameters($Parameters);
        $QueryString = vsprintf($QueryString, $ConvertedParameters);
        return self::Delete($QueryString);
    }

    /**
     * Escape parameters.
     * @param array $Parameters
     * @return array 
     */
    public static function ConvertParameters(array $Parameters) {
        $ConvertedParameters = array();
        foreach ($Parameters as $Parameter) {
            array_push($ConvertedParameters, mysql_escape_string($Parameter));
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
        while ($Data = mysql_fetch_assoc($Result)) {
            $Classname = $ModelTable->GetModelClassName();
            $Model = new $Classname();
            $Model->Fill($Data);
            $List->append($Model);
        }
        return $List;
    }

}

?>