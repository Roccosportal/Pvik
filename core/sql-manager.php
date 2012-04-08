<?php

/**
 * Class that refers to the right function depending on which database is selected.
 */
class SQLManager {

    /**
     * Contains the current used database system.
     * @var string 
     */
    protected static $DatabaseSystem = null;

    /**
     * Returns the current used databse system.
     * @return string 
     */
    public static function GetDatabaseSystem() {
        if (self::$DatabaseSystem == null) {
            if (isset(Core::$Config['MySQL'])) {
                self::$DatabaseSystem = 'MySQL';
                Log::WriteLine('Use MySQL as database system');
            } elseif (isset(Core::$Config['MSSQL'])) {
                self::$DatabaseSystem = 'MSSQL';
                Log::WriteLine('Use MSSQL as database system');
            } elseif (isset(Core::$Config['SQLSRV'])) {
                self::$DatabaseSystem = 'SQLSRV';
                Log::WriteLine('Use SQLSRV as database system');
            }
        }
        return self::$DatabaseSystem;
    }

    /**
     * Executes a statement.
     * @param string $QueryString
     * @return mixed 
     */
    public static function ExecuteQueryString($QueryString) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::ExecuteQueryString($QueryString);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::ExecuteQueryString($QueryString);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::ExecuteQueryString($QueryString);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /**
     * Executes a select statement.
     * @param type $QueryString
     * @return mixed 
     */
    public static function Select($QueryString) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::Select($QueryString);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::Select($QueryString);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::Select($QueryString);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /*
     * Executes a select statement with parameters.
     * @param string $QueryString
     * @param array $Parameters
     * @return mixed 
     */

    public static function SelectWithParameters($QueryString, array $Parameters) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::SelectWithParameters($QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::SelectWithParameters($QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::SelectWithParameters($QueryString, $Parameters);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /**
     * Executes a insert statement
     * @param string $QueryString
     * @return mixed 
     */
    public static function Insert($QueryString) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::Insert($QueryString);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::Insert($QueryString);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::Insert($QueryString);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /**
     * Executes a insert statement with parameters.
     * @param string $QueryString
     * @param array $Parameters
     * @return mixed 
     */
    public static function InsertWithParameters($QueryString, array $Parameters) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::InsertWithParameters($QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::InsertWithParameters($QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::InsertWithParameters($QueryString, $Parameters);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /**
     * Executes a update statement.
     * @param string $QueryString
     * @return mixed 
     */
    public static function Update($QueryString) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::Update($QueryString);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::Update($QueryString);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::Update($QueryString);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /**
     * Executes a update statement with parameters.
     * @param string $QueryString
     * @param array $Parameters
     * @return type 
     */
    public static function UpdateWithParameters($QueryString, array $Parameters) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::UpdateWithParameters($QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::UpdateWithParameters($QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::UpdateWithParameters($QueryString, $Parameters);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /**
     * Executes a delete statement.
     * @param string $QueryString
     * @return mixed 
     */
    public static function Delete($QueryString) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::Delete($QueryString);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::Delete($QueryString);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::Delete($QueryString);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /**
     * Executes a delete statement with parameters
     * @param type $QueryString
     * @param type $Parameters
     * @return type 
     */
    public static function DeleteWithParameters($QueryString, array $Parameters) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::DeleteWithParameters($QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::DeleteWithParameters($QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::DeleteWithParameters($QueryString, $Parameters);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /**
     * Escape parameters.
     * @param array $Parameters
     * @return array 
     */
    public static function ConvertParameters($Paremeters) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::ConvertParameters($Paremeters);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::ConvertParameters($Paremeters);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::ConvertParameters($Paremeters);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

    /**
     * Creates a ModelArray from a select statemet
     * @param ModelTable $ModelTable
     * @param string $QueryString
     * @param array $Parameters
     * @return ModelArray 
     */
    public static function FillList(ModelTable $ModelTable, $QueryString, array $Parameters) {
        if (self::GetDatabaseSystem() == 'MySQL') {
            return MySQLManager::FillList($ModelTable, $QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'MSSQL') {
            return MSSQLManager::FillList($ModelTable, $QueryString, $Parameters);
        } elseif (self::GetDatabaseSystem() == 'SQLSRV') {
            return SQLSRVManager::FillList($ModelTable, $QueryString, $Parameters);
        } else {
            throw new Exception('Couldn\'t find the correct SQLManager. Probably misconfigured config file.');
        }
    }

}

?>