<?php

class SQLBuilder {
    
    public static function CreateWhereStatementByPrimaryKey(ModelTable $ModelTable){
        if(SQLManager::GetDatabaseSystem()=='MySQL'){
            return MySQLBuilder::CreateWhereStatementByPrimaryKey($ModelTable);
        }
        elseif(SQLManager::GetDatabaseSystem()=='MSSQL' || SQLManager::GetDatabaseSystem()=='SQLSRV'){ // SQLSRV and MSSQL using the same SQL syntax 
            return MSSQLBuilder::CreateWhereStatementByPrimaryKey($ModelTable);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLBuilder. Probably misconfigured config file.');
        }
    }
    
     public static function CreateWhereStatementByPrimaryKeys(ModelTable $ModelTable, $Keys){
        if(SQLManager::GetDatabaseSystem()=='MySQL'){
            return MySQLBuilder::CreateWhereStatementByPrimaryKeys($ModelTable, $Keys);
        }
        elseif(SQLManager::GetDatabaseSystem()=='MSSQL' || SQLManager::GetDatabaseSystem()=='SQLSRV'){ // SQLSRV and MSSQL using the same SQL syntax 
            return MSSQLBuilder::CreateWhereStatementByPrimaryKeys($ModelTable, $Keys);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLBuilder. Probably misconfigured config file.');
        }
    }
    
    
   public static function CreateInStatementForKeys(ModelTable $ModelTable, $Field, $Keys){
        if(SQLManager::GetDatabaseSystem()=='MySQL'){
            return MySQLBuilder::CreateInStatementForKeys($ModelTable, $Field, $Keys);
        }
        elseif(SQLManager::GetDatabaseSystem()=='MSSQL' || SQLManager::GetDatabaseSystem()=='SQLSRV'){ // SQLSRV and MSSQL using the same SQL syntax 
            return MSSQLBuilder::CreateInStatementForKeys($ModelTable, $Field, $Keys);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLBuilder. Probably misconfigured config file.');
        }
    }
    
    public static function CreateSelectStatement(ModelTable $ModelTable, $Conditions,$OrderBy){
         if(SQLManager::GetDatabaseSystem()=='MySQL'){
            return MySQLBuilder::CreateSelectStatement($ModelTable, $Conditions, $OrderBy);
        }
        elseif(SQLManager::GetDatabaseSystem()=='MSSQL' || SQLManager::GetDatabaseSystem()=='SQLSRV'){ // SQLSRV and MSSQL using the same SQL syntax 
            return MSSQLBuilder::CreateSelectStatement($ModelTable, $Conditions, $OrderBy);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLBuilder. Probably misconfigured config file.');
        }
    }
    
    public static function CreateInsertStatement(ModelTable $ModelTable, Model $Object){
         if(SQLManager::GetDatabaseSystem()=='MySQL'){
            return MySQLBuilder::CreateInsertStatement($ModelTable, $Object);
        }
        elseif(SQLManager::GetDatabaseSystem()=='MSSQL' || SQLManager::GetDatabaseSystem()=='SQLSRV'){ // SQLSRV and MSSQL using the same SQL syntax 
            return MSSQLBuilder::CreateInsertStatement($ModelTable, $Object);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLBuilder. Probably misconfigured config file.');
        }
    }
    
    public static function CreateUpdateStatement(ModelTable $ModelTable, Model $Object){
        if(SQLManager::GetDatabaseSystem()=='MySQL'){
            return MySQLBuilder::CreateUpdateStatement($ModelTable, $Object);
        }
        elseif(SQLManager::GetDatabaseSystem()=='MSSQL' || SQLManager::GetDatabaseSystem()=='SQLSRV'){ // SQLSRV and MSSQL using the same SQL syntax 
            return MSSQLBuilder::CreateUpdateStatement($ModelTable, $Object);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLBuilder. Probably misconfigured config file.');
        }
    }
    
    public static function CreateDeleteStatement(ModelTable $ModelTable){
        if(SQLManager::GetDatabaseSystem()=='MySQL'){
            return MySQLBuilder::CreateDeleteStatement($ModelTable);
        }
        elseif(SQLManager::GetDatabaseSystem()=='MSSQL' || SQLManager::GetDatabaseSystem()=='SQLSRV'){ // SQLSRV and MSSQL using the same SQL syntax 
            return MSSQLBuilder::CreateDeleteStatement($ModelTable);
        }
        else {
            throw new Exception('Couldn\'t find the correct SQLBuilder. Probably misconfigured config file.');
        }
    }
    
    
}
?>