<?php

class MSSQLManager {
    protected static $Instance;

    protected $Connection;


    protected function __construct(){
        $MSSQL = Core::$Config['MSSQL'];
        
        $this->Connection = mssql_connect($MSSQL['Server'], $MSSQL['Username'], $MSSQL['Password']);
        if (!$this->Connection) {
            throw new Exception('Database error: ' . mssql_get_last_message());
        }
        else {
            if (!mssql_select_db($MSSQL['Database'])){
                throw new Exception('Database error: ' . mssql_get_last_message());
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
            self::$Instance = new MSSQLManager();
        }
    }

    public static function ExecuteQueryString($QueryString){
        self::Connect();
        Log::WriteLine('Executing querystring: ' .$QueryString);
        $Result = mssql_query($QueryString);
        if (!$Result) {
            throw new Exception('Could not execute following statement: '. $QueryString."\n" .'MSSQLError: '. mssql_get_last_message());
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
        return self::mysql_insert_id();
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
            array_push($ConvertedParameters, self::mssql_escape_string($Parameter));
        }
        return $ConvertedParameters;
    }
    
    public static function FillList(ModelTable $ModelTable, $QueryString, $Parameters){
        $Result = SQLManager::SelectWithParameters($QueryString, $Parameters);
        $List = new ModelArray(array(), ArrayObject::STD_PROP_LIST);
        while ($Data = mssql_fetch_assoc($Result)) {
            $Classname = $ModelTable->GetModelClassName() . 'Model';
            $Model = new $Classname();
            $Model->Fill($Data);
            $List->append($Model);
        }
        return $List;
    }
    protected static function mssql_escape_string($String) {
	return str_replace("'","''",$String);
    }
    
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
