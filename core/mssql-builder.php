<?php
/**
 * Builds sql statements according to mssql sql.
 * Uses functions of the mysql builder because statements are similar.
 */
class MSSQLBuilder {

    /**
     * Creates a where statement by a primary key.
     * Example:
     * WHERE Author.AuthorID = '%s'
     * @param ModelTable $ModelTable
     * @return string 
     */
    public static function CreateWhereStatementByPrimaryKey(ModelTable $ModelTable) {
        $Result = MySQLBuilder::CreateWhereStatementByPrimaryKey($ModelTable);
        // convert " to '
        $Result = str_replace('"', "'", $Result);
        return $Result;
    }

    /**
     * Creates a select statement.
     * @param ModelTable $ModelTable
     * @param string $Conditions
     * @param string $OrderBy
     * @return string 
     */
    public static function CreateSelectStatement(ModelTable $ModelTable, $Conditions, $OrderBy) {
        $SQL = "";
        $SQL .= self::CreateSelectPart($ModelTable);
        $SQL .= " ";
        $SQL .= $Conditions;
        $SQL .= " ";
        $SQL .= " ";
        $SQL .= $OrderBy;
        return $SQL;
    }

    /**
     * Creates the select header.
     * @param ModelTable $ModelTable
     * @return string 
     */
    protected static function CreateSelectPart(ModelTable $ModelTable) {
        $SQL = "SELECT ";
        $Count = 1;
        $Helper = $ModelTable->GetFieldDefinitionHelper();
        foreach ($Helper->GetFieldList() as $FieldName) {
            switch ($Helper->GetFieldType($FieldName)) {
                case "Normal":
                case "PrimaryKey":
                case "ForeignKey":
                    if ($Count > 1) {
                        // add , at the end
                        $SQL .= ", ";
                    }
                    $SQL .= self::SQLAttribute($ModelTable, $FieldName);
                    $Count++;
                    break;
                case "ManyForeignObjects":
                    if ($Count > 1) {
                        // add , at the end
                        $SQL .= ", ";
                    }
                    $ForeignModelTable = $Helper->GetModelTable($FieldName);
                    // generate group_conact
                    $SQL .= " " . $FieldName . " = replace ((SELECT " . self::SQLAttribute($ForeignModelTable, $ForeignModelTable->GetPrimaryKeyName(), "[data()]") .
                            " FROM " . $ForeignModelTable->GetTableName() .
                            " WHERE  " . $ForeignModelTable->GetTableName() . "." . $Helper->GetForeignKeyFieldName($FieldName) . " = " . $ModelTable->GetTableName() . "." . $ModelTable->GetPrimaryKeyName() .
                            " FOR xml path('')), ' ', ',') ";

                    $Count++;
                    break;
            }
        }
        $SQL .= " FROM " . $ModelTable->GetTableName();

        return $SQL;
    }

    /**
     * Creates a sql attribute part.
     * Example:
     * Authors.AuthorID
     * or
     * Authors.AuthorID as ID
     * @param ModelTable $ModelTable
     * @param string $Attribute
     * @param string $Alias
     * @param string $Table
     * @return string 
     */
    protected static function SQLAttribute(ModelTable $ModelTable, $Attribute, $Alias = "", $Table = "") {
        return MySQLBuilder::SQLAttribute($ModelTable, $Attribute, $Alias, $Table);
    }

    /**
     * Creates a insert statement.
     * @param ModelTable $ModelTable
     * @param Model $Object
     * @return array $Result['SQL'], $Result['Parameters']
     */
    public static function CreateInsertStatement(ModelTable $ModelTable, Model $Object) {
        $Result = MySQLBuilder::CreateInsertStatement($ModelTable, $Object);
        // convert " to '
        $Result['SQL'] = str_replace('"', "'", $Result['SQL']);
        return $Result;
    }

    /**
     * Creates an update statement.
     * @param ModelTable $ModelTable
     * @param Model $Object
     * @return array $Result['SQL'], $Result['Parameters']
     */
    public static function CreateUpdateStatement(ModelTable $ModelTable, Model $Object) {
        $Result = MySQLBuilder::CreateUpdateStatement($ModelTable, $Object);
        // convert " to '
        $Result['SQL'] = str_replace('"', "'", $Result['SQL']);
        return $Result;
    }

    /**
     * Creates a delete statement.
     * @param ModelTable $ModelTable
     * @return string 
     */
    public static function CreateDeleteStatement(ModelTable $ModelTable) {
        $Result = MySQLBuilder::CreateDeleteStatement($ModelTable);
        // convert " to '
        $Result = str_replace('"', "'", $Result);
        return $Result;
    }

    /**
     * Creates a where statement according to primary keys.
     * 
     * @param ModelTable $ModelTable
     * @param array $Keys
     * @return array $Result['SQL'], $Result['Parameters'] 
     */
    public static function CreateWhereStatementByPrimaryKeys(ModelTable $ModelTable,array $Keys) {
        return MySQLBuilder::CreateWhereStatementByPrimaryKeys($ModelTable, $Keys);
    }

    /**
     * Create a in statement for keys
     * @param ModelTable $ModelTable
     * @param string $Field
     * @param array $Keys
     * @return array $Result['SQL'], $Result['Parameters']
     */
    public static function CreateInStatementForKeys(ModelTable $ModelTable, $Field,array $Keys) {
        $Result = MySQLBuilder::CreateInStatementForKeys($ModelTable, $Field, $Keys);
        // convert " to '
        $Result['SQL'] = str_replace('"', "'", $Result['SQL']);
        return $Result;
    }

}

?>