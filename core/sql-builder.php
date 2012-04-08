<?php
/**
 * Class that refers to the right function depending on which database is selected.
 */
class SQLBuilder {
    
    /**
     * Creates a where statement by a primary key.
     * Example:
     * WHERE Author.AuthorID = "%s"
     * @param ModelTable $ModelTable
     * @return string 
     */
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
    
    /**
     * Creates a where statement according to primary keys.
     * 
     * @param ModelTable $ModelTable
     * @param array $Keys
     * @return array $Result['SQL'], $Result['Parameters'] 
     */
     public static function CreateWhereStatementByPrimaryKeys(ModelTable $ModelTable,array $Keys){
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
    
    /**
     * Create a in statement for keys
     * @param ModelTable $ModelTable
     * @param string $Field
     * @param array $Keys
     * @return array $Result['SQL'], $Result['Parameters']
     */
   public static function CreateInStatementForKeys(ModelTable $ModelTable, $Field,array $Keys){
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
    
        /**
     * Creates a select statement.
     * @param ModelTable $ModelTable
     * @param string $Conditions
     * @param string $OrderBy
     * @return string 
     */
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
    /**
     * Creates a insert statement.
     * @param ModelTable $ModelTable
     * @param Model $Object
     * @return array $Result['SQL'], $Result['Parameters']
     */
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
    
    /**
     * Creates an update statement.
     * @param ModelTable $ModelTable
     * @param Model $Object
     * @return array $Result['SQL'], $Result['Parameters']
     */
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
    
    /**
     * Creates a delete statement.
     * @param ModelTable $ModelTable
     * @return string 
     */
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