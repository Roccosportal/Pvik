<?php

class SQLManager {
    protected static $DatabaseSystem = null;


    public static function GetDatabaseSystem(){
        if(self::$DatabaseSystem==null){
            if(isset(Core::$Config['MySQL'])){
                self::$DatabaseSystem = 'MySQL';
                Log::WriteLine('Use MySQL as database system');
            }
            elseif(isset(Core::$Config['MSSQL'])){
                 self::$DatabaseSystem = 'MSSQL';
                 Log::WriteLine('Use MSSQL as database system');
            }
             elseif(isset(Core::$Config['SQLSRV'])){
                self::$DatabaseSystem = 'SQLSRV';
                 Log::WriteLine('Use SQLSRV as database system');
            }    
        }
        return self::$DatabaseSystem;
    }
    
    public static function ExecuteQueryString($QueryString){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::ExecuteQueryString($QueryString);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::ExecuteQueryString($QueryString);
        }
        elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::ExecuteQueryString($QueryString);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    public static function Select($QueryString){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::Select($QueryString);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::Select($QueryString);
        }
         elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::Select($QueryString);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }
    
    public static function SelectWithParameters($QueryString, $Parameters){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::SelectWithParameters($QueryString, $Parameters);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::SelectWithParameters($QueryString, $Parameters);
        }
        elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::SelectWithParameters($QueryString, $Parameters);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }
    
    public static function Insert($QueryString){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::Insert($QueryString);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::Insert($QueryString);
        }
         elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::Insert($QueryString);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    public static function InsertWithParameters($QueryString, $Parameters){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::InsertWithParameters($QueryString, $Parameters);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::InsertWithParameters($QueryString, $Parameters);
        }
         elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::InsertWithParameters($QueryString, $Parameters);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }
    
    public static function Update($QueryString){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::Update($QueryString);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::Update($QueryString);
        }
          elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::Update($QueryString);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    public static function UpdateWithParameters($QueryString, $Parameters){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::UpdateWithParameters($QueryString, $Parameters);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::UpdateWithParameters($QueryString, $Parameters);
        }
        elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::UpdateWithParameters($QueryString, $Parameters);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    public static function Delete($QueryString){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::Delete($QueryString);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::Delete($QueryString);
        }
        elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::Delete($QueryString);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    public static function DeleteWithParameters($QueryString, $Parameters){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::DeleteWithParameters($QueryString, $Parameters);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::DeleteWithParameters($QueryString, $Parameters);
        }
         elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::DeleteWithParameters($QueryString, $Parameters);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }
    
    public static function ConvertParameters($Paremeters){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::ConvertParameters($Paremeters);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::ConvertParameters($Paremeters);
        }
        elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::ConvertParameters($Paremeters);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }
    
    public static function FillList(ModelTable $ModelTable, $QueryString, $Parameters){
        if(self::GetDatabaseSystem()=='MySQL'){
            return MySQLManager::FillList($ModelTable, $QueryString, $Parameters);
        }
        elseif(self::GetDatabaseSystem()=='MSSQL'){
            return MSSQLManager::FillList($ModelTable, $QueryString, $Parameters);
        }
         elseif(self::GetDatabaseSystem()=='SQLSRV'){
            return SQLSRVManager::FillList($ModelTable, $QueryString, $Parameters);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }
    
}

?>
