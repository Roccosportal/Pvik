<?php

class MySQLManager {
    protected static $Instance;

    protected $Connection;


    protected function __construct(){
        $MySQL = Core::$Config['MySQL'];
        $this->Connection = mysql_connect($MySQL['Server'], $MySQL['Username'], $MySQL['Password']);
        if (!$this->Connection) {
            throw new Exception('Database error: ' . mysql_error());
        }
        else {
            mysql_set_charset('utf8', $this->Connection); 
            if (!mysql_select_db($MySQL['Database'])){
                throw new Exception('Database error: ' . mysql_error());
            }
        }
    }

    /*
    protected function __destruct(){
        /*
        if(!$this->Connection){
            mysql_close($this->Connection);
        }
         * *
         
    }
    */

    protected static function Connect(){
        if(self::$Instance==null){
            self::$Instance = new MySQLManager();
        }
    }

    public static function ExecuteQueryString($QueryString){
        self::Connect();
        Log::WriteLine('Executing querystring: ' .$QueryString);
        $Result = mysql_query($QueryString);
        if (!$Result) {
            throw new Exception('Could not execute following statement: '. $QueryString."\n" .'MySQLError: '. mysql_error());
        }
        return $Result;
    }

    public static function Select($QueryString){
        return self::ExecuteQueryString($QueryString);
    }

    public static function SelectWithParameters($QueryString, $Parameters ){
        $ConvertedParameters =self::ConvertParameters($Parameters);
        $QueryString = vsprintf($QueryString, $ConvertedParameters);
        return self::Select($QueryString);
    }
    
    public static function Insert($QueryString){
        self::ExecuteQueryString($QueryString);
        return mysql_insert_id();
    }

    public static function InsertWithParameters($QueryString, $Parameters){
        $ConvertedParameters = self::ConvertParameters($Parameters);
        $QueryString = vsprintf($QueryString, $ConvertedParameters);
        return self::Insert($QueryString);
    }
    
    public static function Update($QueryString){
        return self::ExecuteQueryString($QueryString);
    }

    public static function UpdateWithParameters($QueryString, $Parameters ){
        $ConvertedParameters =self::ConvertParameters($Parameters);
        $QueryString = vsprintf($QueryString, $ConvertedParameters);
        return self::Update($QueryString);
    }
    
    public static function Delete($QueryString){
        return self::ExecuteQueryString($QueryString);
    }

    public static function DeleteWithParameters($QueryString, $Parameters){
        $ConvertedParameters =self::ConvertParameters($Parameters);
        $QueryString = vsprintf($QueryString, $ConvertedParameters);
        return self::Delete($QueryString);
    }

  
    public static function ConvertParameters($Paremeters){
        $ConvertedParameters = array();
        foreach($Paremeters as $Parameter){
            array_push($ConvertedParameters, mysql_escape_string($Parameter));
        }
        return $ConvertedParameters;
    }
    
    public static function FillList(ModelTable $ModelTable, $QueryString, $Parameters){
        $Result = SQLManager::SelectWithParameters($QueryString, $Parameters);
        $List = new ModelArray(array(), ArrayObject::STD_PROP_LIST);
        while ($Data = mysql_fetch_assoc($Result)) {
            $Classname = $ModelTable->GetModelClassName() . 'Model';
            $Model = new $Classname();
            $Model->Fill($Data);
            $List->append($Model);
        }
        return $List;
    }
}
?>