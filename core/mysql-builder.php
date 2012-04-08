<?php

class MySQLBuilder {

    /**
     * Creates a where statement by a primary key.
     * Example:
     * WHERE Author.AuthorID = "%s"
     * @param ModelTable $ModelTable
     * @return string 
     */
    public static function CreateWhereStatementByPrimaryKey(ModelTable $ModelTable) {
        return 'WHERE ' . $ModelTable->GetTableName() . '.' . $ModelTable->GetPrimaryKeyName() . ' = "%s"';
    }

    /**
     * Creates a select statement.
     * @param ModelTable $ModelTable
     * @param string $Conditions
     * @param string $OrderBy
     * @return string 
     */
    public static function CreateSelectStatement(ModelTable $ModelTable, $Conditions, $OrderBy) {
        $SQL = '';
        $SQL .= self::CreateSelectPart($ModelTable);
        $SQL .= ' ';
        $SQL .= $Conditions;
        $SQL .= ' ';
        $SQL .= self::CreateGroupByStatement($ModelTable);
        $SQL .= ' ';
        $SQL .= $OrderBy;
        return $SQL;
    }

    /**
     * Creates the select header.
     * @param ModelTable $ModelTable
     * @return string 
     */
    protected static function CreateSelectPart(ModelTable $ModelTable) {
        $SQL = 'SELECT ';
        $Count = 1;
        $Join = false;
        $AliasArray = array();
        $Helper = $ModelTable->GetFieldDefinitionHelper();
        foreach ($Helper->GetFieldList() as $FieldName) {
            switch ($Helper->GetFieldType($FieldName)) {
                case 'Normal':
                case 'PrimaryKey':
                case 'ForeignKey':
                    if ($Count > 1) {
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .= self::SQLAttribute($ModelTable, $FieldName);
                    $Count++;
                    break;
                case 'ManyForeignObjects':
                    if ($Count > 1) {
                        // add , at the end
                        $SQL .= ', ';
                    }
                    // simeple creation for a unique alias
                    $Alias = '';
                    for ($i = 0; $i < count($AliasArray) + 1; $i++) {
                        $Alias .= 't';
                    }
                    $AliasArray[$FieldName] = $Alias;
                    // get definition for the foreign table

                    $ForeignModelTable = $Helper->GetModelTable($FieldName);
                    // generate group_conact
                    $SQL.= 'GROUP_CONCAT(DISTINCT ' . self::SQLAttribute($ModelTable, $ForeignModelTable->GetPrimaryKeyName(), '', $Alias) . ') as ' . $FieldName;
                    $Join = true;
                    $Count++;
                    break;
            }
        }
        $SQL .= ' FROM ' . $ModelTable->GetTableName();


        if ($Join) {
            // add joins
            foreach ($Helper->GetManyForeignObjectsFieldList() as $FieldName) {
                $ForeignModelTable = $Helper->GetModelTable($FieldName);
                $SQL .= ' LEFT JOIN ' . $ForeignModelTable->GetTableName() . ' as ' . $AliasArray[$FieldName]
                        . ' ON ' . self::SQLAttribute($ModelTable, $Helper->GetForeignKeyFieldName($FieldName), '', $AliasArray[$FieldName])
                        . ' = ' . self::SQLAttribute($ModelTable, $ModelTable->GetPrimaryKeyName(), '', $ModelTable->GetTableName());
            }
        }
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
    public static function SQLAttribute(ModelTable $ModelTable, $Attribute, $Alias = '', $Table = '') {
        if ($Table == '') {
            $Table = $ModelTable->GetTableName();
        }
        $SQL = $Table . '.' . $Attribute;
        if ($Alias != '') {
            $SQL .= ' as ' . $Alias;
        }
        return $SQL;
    }

    /**
     * Creates group statement.
     * @param ModelTable $ModelTable
     * @return string 
     */
    public static function CreateGroupByStatement(ModelTable $ModelTable) {
        $Count = 1;
        $SQL = '';
        $Helper = $ModelTable->GetFieldDefinitionHelper();
        foreach ($Helper->GetManyForeignObjectsFieldList() as $FieldName) {
            if ($Count > 1) {
                // add , at the end
                $SQL .= ', ';
            }
            $SQL .= self::SQLAttribute($ModelTable, $ModelTable->GetPrimaryKeyName());
            $Count++;
        }
        // if we have a group by part
        if ($Count > 1) {
            $SQL = 'GROUP BY ' . $SQL;
        }
        return $SQL;
    }

    /**
     * Creates a insert statement.
     * @param ModelTable $ModelTable
     * @param Model $Object
     * @return array $Result['SQL'], $Result['Parameters']
     */
    public static function CreateInsertStatement(ModelTable $ModelTable, Model $Object) {
        $SQL = 'INSERT INTO ' . $ModelTable->GetTableName();
        $Helper = $ModelTable->GetFieldDefinitionHelper();
        // create column list
        $SQL .= ' (';
        $Count = 1;
        foreach ($Helper->GetFieldList() as $FieldName) {
            switch ($Helper->GetFieldType($FieldName)) {
                case 'Normal':
                case 'ForeignKey':
                    if ($Count > 1) {
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .= $FieldName;
                    $Count++;
                    break;
                case 'PrimaryKey':
                    // only insert a value if it is a guid otherwise ignore
                    // the primarykey will be set on the database
                    if ($Helper->IsGuid($FieldName)) {
                        if ($Count > 1) {
                            // add , at the end
                            $SQL .= ', ';
                        }
                        $SQL .= $FieldName;
                        $Count++;
                    }
                    break;
            }
        }
        $SQL .= ')';
        // create column values
        $SQL .= ' VALUES (';
        $Count = 1;
        $Parameters = array();
        foreach ($Helper->GetFieldList() as $FieldName) {
            switch ($Helper->GetFieldType($FieldName)) {
                case 'Normal':
                case 'ForeignKey':
                    if ($Count > 1) {
                        // add , at the end
                        $SQL .= ', ';
                    }
                    if (is_bool($Object->$FieldName)) {
                        if ($Object->$FieldName == true)
                            $SQL .= 'TRUE';
                        else
                            $SQL .= 'FALSE';
                    }
                    elseif ($Object->$FieldName !== null) {
                        $SQL .= '"%s"';
                        array_push($Parameters, $Object->$FieldName);
                    } else {
                        $SQL .= 'NULL';
                    }
                    $Count++;
                    break;
                case 'PrimaryKey':
                    // only insert a value if it is a guid otherwise ignore
                    // the primarykey will be set on the database
                    if ($Helper->IsGuid($FieldName)) {
                        if ($Count > 1) {
                            // add , at the end
                            $SQL .= ', ';
                        }
                        $SQL .= '"%s"';
                        array_push($Parameters, Core::CreateGuid());
                        $Count++;
                    }
                    break;
            }
        }
        $SQL .= ')';
        return array('SQL' => $SQL, 'Parameters' => $Parameters);
    }

    /**
     * Creates an update statement.
     * @param ModelTable $ModelTable
     * @param Model $Object
     * @return array $Result['SQL'], $Result['Parameters']
     */
    public static function CreateUpdateStatement(ModelTable $ModelTable, Model $Object) {
        $SQL = 'UPDATE ' . $ModelTable->GetTableName() . ' SET ';
        $Count = 1;
        $Parameters = array();
        $Helper = $ModelTable->GetFieldDefinitionHelper();
        foreach ($Helper->GetFieldList() as $FieldName) {
            switch ($Helper->GetFieldType($FieldName)) {
                case 'Normal':
                    if ($Count > 1) {
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $Data = $Object->GetFieldData($FieldName);
                    if (is_bool($Data)) {
                        if ($Data == true)
                            $SQL .= $FieldName . '= TRUE';
                        else
                            $SQL .= $FieldName . '= FALSE';
                    }
                    elseif ($Data !== null) {
                        $SQL .= $FieldName . ' = "%s"';
                        array_push($Parameters, $Data);
                    } else {
                        $SQL .= $FieldName . ' = NULL';
                    }
                    $Count++;
                    break;
                case 'ForeignKey':
                    if ($Count > 1) {
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $Data = $Object->GetFieldData($FieldName);
                    if (is_bool($Data)) {
                        if ($Data == true)
                            $SQL .= $FieldName . '= TRUE';
                        else
                            $SQL .= $FieldName . '= FALSE';
                    }
                    elseif ($Data !== null) {
                        $SQL .= $FieldName . ' = "' . $Data . '"';
                    } else {
                        $SQL .= $FieldName . ' = NULL';
                    }
                    $Count++;
                    break;
            }
        }
        $SQL .= ' ' . self::CreateWhereStatementByPrimaryKey($ModelTable);
        array_push($Parameters, $Object->GetFieldData($ModelTable->GetPrimaryKeyName()));
        return array('SQL' => $SQL, 'Parameters' => $Parameters);
    }

    /**
     * Creates a delete statement.
     * @param ModelTable $ModelTable
     * @return string 
     */
    public static function CreateDeleteStatement(ModelTable $ModelTable) {
        return 'DELETE FROM ' . $ModelTable->GetTableName() . ' WHERE ' . $ModelTable->GetPrimaryKeyName() . ' = "%s"';
    }

    /**
     * Creates a where statement according to primary keys.
     * 
     * @param ModelTable $ModelTable
     * @param array $Keys
     * @return array $Result['SQL'], $Result['Parameters'] 
     */
    public static function CreateWhereStatementByPrimaryKeys(ModelTable $ModelTable, array $Keys) {
        $Result = self::CreateInStatementForKeys($ModelTable, $ModelTable->GetPrimaryKeyName(), $Keys);
        $Result['SQL'] = "WHERE " . $Result['SQL'];
        return $Result;
    }

    /**
     * Create a in statement for keys
     * @param ModelTable $ModelTable
     * @param string $Field
     * @param array $Keys
     * @return array $Result['SQL'], $Result['Parameters']
     */
    public static function CreateInStatementForKeys(ModelTable $ModelTable, $FieldName, array $Keys) {
        $SQL = $ModelTable->GetTableName() . "." . $FieldName . " IN ( ";
        $Count = 0;
        $Parameters = array();
        foreach ($Keys as $Key) {
            if ($Count != 0) {
                $SQL .= ",";
            }
            $SQL .= "'%s'";
            array_push($Parameters, $Key);
            $Count++;
        }
        $SQL .= ')';
        return array('SQL' => $SQL, 'Parameters' => $Parameters);
    }

}

?>