<?php


class MySQLBuilder {
    
    public static function CreateWhereStatementByPrimaryKey(ModelTable $ModelTable){
       return 'WHERE ' . $ModelTable->GetTableName()  . '.' . $ModelTable->GetPrimaryKeyName() . ' = "%s"';
    }
    
    public static function CreateSelectStatement(ModelTable $ModelTable, $Conditions,$OrderBy){
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
    
    protected static function CreateSelectPart(ModelTable $ModelTable){
        $SQL = 'SELECT ';
        $Count = 1;
        $Join = false;
        $AliasArray = array();
        foreach ($ModelTable->GetDataDefinition() as $Key => $Definition) {
            switch ($Definition['Type']) {
                case 'Normal':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .= self::SQLAttribute($ModelTable,$Key);
                    $Count++;
                    break;
                case 'PrimaryKey':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .= self::SQLAttribute($ModelTable,$Key);
                    $Count++;
                    break;
                case 'ForeignKey':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .=  self::SQLAttribute($ModelTable,$Key);
                    $Count++;
                    break;
                case 'ManyForeignObjects':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    // simeple creation for a unique alias
                    $Alias = '';
                    for ($i = 0; $i < count($AliasArray) + 1; $i++) {
                        $Alias .= 't';
                    }
                    $AliasArray[$Key] = $Alias;
                    // get definition for the foreign table
                    $ForeignModelTable =  ModelTable::Get($Definition['ModelTable']);
                    // generate group_conact
                    $SQL.= 'GROUP_CONCAT(' . self::SQLAttribute($ModelTable,$ForeignModelTable->GetPrimaryKeyName(), '',$Alias  ) .') as '. $Key;
                    $Join = true;
                    $Count++;
                    break;
            }
            
        }
        $SQL .=  ' FROM ' . $ModelTable->GetTableName() ;


        if($Join){
            // add joins
            foreach ($ModelTable->GetDataDefinition() as $Key => $Definition) {
                 if ($Definition['Type']=='ManyForeignObjects') {
                     $ForeignModelTable =  ModelTable::Get($Definition['ModelTable']);
                         $SQL .= ' LEFT JOIN ' . $ForeignModelTable->GetTableName() . ' as ' . $AliasArray[$Key]
                                . ' ON ' . self::SQLAttribute($ModelTable,$Definition['ForeignKey'],'',$AliasArray[$Key])
                                . ' = ' . self::SQLAttribute($ModelTable,$ModelTable->GetPrimaryKeyName(),'',$ModelTable->GetTableName());
    

                 }
            }
        }
        return $SQL;
    }
    
    protected static function SQLAttribute(ModelTable $ModelTable,$Attribute, $Alias = '', $Table = ''){
        if($Table==''){
            $Table = $ModelTable->GetTableName();
        }
        $SQL = $Table . '.'. $Attribute;
        if($Alias!=''){
            $SQL .= ' as '. $Alias;
        }
        return $SQL;
    }
    
    protected static function CreateGroupByStatement(ModelTable $ModelTable){
        $Count = 1;
        $SQL = '';
        foreach ($ModelTable->GetDataDefinition() as $Key => $Definition) {
             if ($Definition['Type']=='ManyForeignObjects') {
                if($Count>1){
                     // add , at the end
                    $SQL .= ', ';
                }
                $SQL .=  self::SQLAttribute($ModelTable,$ModelTable->GetPrimaryKeyName());
                $Count++;
             }
        }
        // if we have a group by part
        if($Count>1){
            $SQL = 'GROUP BY '. $SQL;
        }
        return $SQL;
    }

    public static function CreateInsertStatement(ModelTable $ModelTable, Model $Object){
        $SQL = 'INSERT INTO ' . $ModelTable->GetTableName();
        $DataDefinition = $ModelTable->GetDataDefinition();
        // create column list
        $SQL .= ' (';
        $Count = 1;
        foreach($DataDefinition as $Key => $Definition){
            switch($Definition['Type']){
                case 'Normal':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .= $Key;
                    $Count++;
                    break;
               case 'ForeignKey':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .= $Key;
                    $Count++;
                    break;
               case 'PrimaryKey':
                   // only insert a value if it is a guid otherwise ignore
                   // the primarykey will be set on the database
                   if(isset($Definition['IsGuid'])&&$Definition['IsGuid']==true){
                        if($Count>1){
                            // add , at the end
                            $SQL .= ', ';
                        }
                        $SQL .= $Key;
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
        foreach($DataDefinition as $Key => $Definition){
            switch($Definition['Type']){
                case 'Normal':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    if(is_bool($Object->$Key)){
                        if($Object->$Key==true)
                            $SQL .= 'TRUE';
                        else
                             $SQL .= 'FALSE';
                    }
                    elseif($Object->$Key!==null){
                        //$SQL .= '"'. $Object->$Key . '"';
                        $SQL .= '"%s"';
                        array_push($Parameters, $Object->$Key);
                    }
                    else {
                        $SQL .= 'NULL';
                    }
                    $Count++;
                    break;
               case 'ForeignKey':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    if(is_bool($Object->$Key)){
                        if($Object->$Key==true)
                            $SQL .= 'TRUE';
                        else
                             $SQL .= 'FALSE';
                    }
                    elseif($Object->$Key!==null){
                        $SQL .= '"%s"';
                        array_push($Parameters, $Object->$Key);
                    }
                    else {
                        $SQL .= 'NULL';
                    }
                    $Count++;
                    break;
              case 'PrimaryKey':
                   // only insert a value if it is a guid otherwise ignore
                   // the primarykey will be set on the database
                   if(isset($Definition['IsGuid'])&&$Definition['IsGuid']==true){
                        if($Count>1){
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
        return array ('SQL' => $SQL, 'Parameters' => $Parameters);
    }
    
    public static function CreateUpdateStatement(ModelTable $ModelTable, Model $Object){
        $SQL = 'UPDATE '. $ModelTable->GetTableName() . ' SET ';
        $Count = 1;
        $Parameters = array();
        foreach($ModelTable->GetDataDefinition() as $Key => $Definition){
            switch($Definition['Type']){
                case 'Normal':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $Data =  $Object->GetObjectData($Key);
                    if(is_bool($Data)){
                        if($Data==true)
                            $SQL .= $Key . '= TRUE';
                        else
                             $SQL .= $Key . '= FALSE';
                    }
                    elseif($Data!==null){
                        //$SQL .= $Key .' = "' . $Data. '"';
                        $SQL .= $Key .' = "%s"';
                        array_push($Parameters, $Data);
                    }
                    else {
                        $SQL .= $Key .' = NULL';
                    }
                    $Count++;
                    break;
                case 'ForeignKey':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $Data =  $Object->GetObjectData($Key);
                    if(is_bool($Data)){
                        if($Data==true)
                            $SQL .= $Key . '= TRUE';
                        else
                             $SQL .= $Key . '= FALSE';
                    }
                    elseif($Data!==null){
                        $SQL .= $Key .' = "' . $Data. '"';
                    }
                    else {
                        $SQL .= $Key .' = NULL';
                    }
                    $Count++;
                    break;
            }
        }
        //$SQL .= ' WHERE '. $ModelTable->GetPrimaryKeyName() . ' = "%s"';
        $SQL .= ' '. self::CreateWhereStatementByPrimaryKey($ModelTable);
        array_push($Parameters, $Object->GetObjectData($ModelTable->GetPrimaryKeyName()));
        return array ('SQL' => $SQL, 'Parameters' => $Parameters);
    }
   
    public static function CreateDeleteStatement(ModelTable $ModelTable){
        return 'DELETE FROM '. $ModelTable->GetTableName() . ' WHERE '. $ModelTable->GetPrimaryKeyName() . ' = "%s"';
    }
}

?>
