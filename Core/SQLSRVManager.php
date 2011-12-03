<?php

class SQLSRVManager {
    protected static $Instance;

    protected $Connection;


    protected function __construct(){
        $SQLSRV = Core::$Config['SQLSRV'];
        
        $ConnectionOptions = array('Database'=>$SQLSRV['Database'], 'UID' => $SQLSRV['Username'], 'PWD' => $SQLSRV['Password'],  "CharacterSet" => "UTF-8");
        $this->Connection = sqlsrv_connect( $SQLSRV['Server'], $ConnectionOptions);
        if ($this->Connection===false) {
            throw new Exception('Database error: ' . self::FormatErrors(sqlsrv_errors()));
        }
    }
    
    public function GetConnection(){
        return $this->Connection;
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
            self::$Instance = new SQLSRVManager();
        }
        return self::$Instance->GetConnection();
    }

    public static function ExecuteQueryString($QueryString){
        $Connection = self::Connect();
        Log::WriteLine('Executing querystring: ' .$QueryString);
        $Result =  sqlsrv_query($Connection, $QueryString);;
        if ($Result===false) {
            throw new Exception('Could not execute following statement: '. $QueryString."\n" .'SQLSRVError: '. self::FormatErrors(sqlsrv_errors()));
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
        return self::sqlsrv_insert_id();
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
        while ($Data = sqlsrv_fetch_array($Result)) {
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
    
    protected static function sqlsrv_insert_id() {
        $ID = 0;
        $Result = self::ExecuteQueryString("SELECT @@identity AS id");
        if ($Row = sqlsrv_fetch_array($Result)) {
            $ID = $Row["id"];
        }
        return $ID;
    } 
    
    protected static function FormatErrors( $Errors )
    {
        $ErrorMessage = "Error information: \n"; 
        foreach ( $Errors as $Error )
        {
             $ErrorMessage .= "SQLSTATE: ".$Error['SQLSTATE']."\n";
             $ErrorMessage .= "Code: ".$Error['code']."\n";
             $ErrorMessage .= "Message: ".$Error['message']."\n";
        }
        return $ErrorMessage;
    }
}
?>