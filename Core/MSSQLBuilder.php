<?php


class MSSQLBuilder {
    
    public static function CreateWhereStatementByPrimaryKey(ModelTable $ModelTable){
        return "WHERE " . $ModelTable->GetTableName()  . "." . $ModelTable->GetPrimaryKeyName() . " = '%s'";
    }
    
    public static function CreateSelectStatement(ModelTable $ModelTable, $Conditions,$OrderBy){
        $SQL = "";
        $SQL .= self::CreateSelectPart($ModelTable);
        $SQL .= " ";
        $SQL .= $Conditions;
        $SQL .= " ";
        //$SQL .= self::CreateGroupByStatement($ModelTable);
        $SQL .= " ";
        $SQL .= $OrderBy;
        return $SQL;
    }

    protected static function CreateSelectPart(ModelTable $ModelTable){
        $SQL = "SELECT ";
        $Count = 1;
        foreach ($ModelTable->GetDataDefinition() as $Key => $Definition) {
            switch ($Definition["Type"]) {
                case "Normal":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
                    $SQL .= self::SQLAttribute($ModelTable,$Key);
                    $Count++;
                    break;
                case "PrimaryKey":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
                    $SQL .= self::SQLAttribute($ModelTable,$Key);
                    $Count++;
                    break;
                case "ForeignKey":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
                    $SQL .=  self::SQLAttribute($ModelTable,$Key);
                    $Count++;
                    break;
                case "ManyForeignObjects":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
 
                    // get definition for the foreign table
                    $ForeignModelTable =  ModelTable::Get($Definition["ModelTable"]);
                    // generate group_conact
                    $SQL .= " " . $Key . " = replace ((SELECT ". self::SQLAttribute($ForeignModelTable,$ForeignModelTable->GetPrimaryKeyName(), "[data()]" ) .
                              " FROM ". $ForeignModelTable->GetTableName().
                              " WHERE  ". $ForeignModelTable->GetTableName()."." .$Definition["ForeignKey"] ." = ". $ModelTable->GetTableName().".". $ModelTable->GetPrimaryKeyName() .
                              " FOR xml path('')), ' ', ',') ";
                    
                    
                    $Count++;
                    break;
            }
            
        }
        $SQL .=  " FROM " . $ModelTable->GetTableName() ;
     
        return $SQL;
    }
    
    protected static function SQLAttribute(ModelTable $ModelTable,$Attribute, $Alias = "", $Table = ""){
        if($Table==""){
            $Table = $ModelTable->GetTableName();
        }
        $SQL = $Table . ".". $Attribute;
        if($Alias!=""){
            $SQL .= " as ". $Alias;
        }
        return $SQL;
    }
    
    protected static function CreateGroupByStatement(ModelTable $ModelTable){
        $Count = 1;
        $SQL = "";
        foreach ($ModelTable->GetDataDefinition() as $Key => $Definition) {
             if ($Definition["Type"]=="ManyForeignObjects") {
                if($Count>1){
                     // add , at the end
                    $SQL .= ", ";
                }
                $SQL .=  self::SQLAttribute($ModelTable,$ModelTable->GetPrimaryKeyName());
                $Count++;
             }
        }
        // if we have a group by part
        if($Count>1){
            $SQL = "GROUP BY ". $SQL;
        }
        return $SQL;
    }

   public static function CreateInsertStatement(ModelTable $ModelTable, Model $Object){
        $SQL = "INSERT INTO " . $ModelTable->GetTableName();
        $DataDefinition = $ModelTable->GetDataDefinition();
        // create column list
        $SQL .= " (";
        $Count = 1;
        foreach($DataDefinition as $Key => $Definition){
            switch($Definition["Type"]){
                case "Normal":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
                    $SQL .= $Key;
                    $Count++;
                    break;
               case "ForeignKey":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
                    $SQL .= $Key;
                    $Count++;
                    break;
               case "PrimaryKey":
                   // only insert a value if it is a guid otherwise ignore
                   // the primarykey will be set on the database
                   if(isset($Definition["IsGuid"])&&$Definition["IsGuid"]==true){
                        if($Count>1){
                            // add , at the end
                            $SQL .= ", ";
                        }
                        $SQL .= $Key;
                        $Count++;
                   }
                   break;
            }
        }
        $SQL .= ")";
        // create column values
        $SQL .= " VALUES (";
        $Count = 1;
        $Parameters = array();
        foreach($DataDefinition as $Key => $Definition){
            switch($Definition["Type"]){
                case "Normal":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
                    if(is_bool($Object->$Key)){
                        if($Object->$Key==true)
                            $SQL .= "TRUE";
                        else
                             $SQL .= "FALSE";
                    }
                    elseif($Object->$Key!==null){
                        //$SQL .= """. $Object->$Key . """;
                        $SQL .= "'%s'";
                        array_push($Parameters, $Object->$Key);
                    }
                    else {
                        $SQL .= "NULL";
                    }
                    $Count++;
                    break;
               case "ForeignKey":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
                    if(is_bool($Object->$Key)){
                        if($Object->$Key==true)
                            $SQL .= "TRUE";
                        else
                             $SQL .= "FALSE";
                    }
                    elseif($Object->$Key!==null){
                        $SQL .= "'%s'";
                        array_push($Parameters, $Object->$Key);
                    }
                    else {
                        $SQL .= "NULL";
                    }
                    $Count++;
                    break;
              case "PrimaryKey":
                   // only insert a value if it is a guid otherwise ignore
                   // the primarykey will be set on the database
                   if(isset($Definition["IsGuid"])&&$Definition["IsGuid"]==true){
                        if($Count>1){
                            // add , at the end
                            $SQL .= ", ";
                        }
                        $SQL .= "'%s'";
                        array_push($Parameters, Core::CreateGuid());
                        $Count++;
                   }
                   break;
            }
        }
        $SQL .= ")";
        return array ("SQL" => $SQL, "Parameters" => $Parameters);
    }
    
    public static function CreateUpdateStatement(ModelTable $ModelTable, Model $Object){
        $SQL = "UPDATE ". $ModelTable->GetTableName() . " SET ";
        $Count = 1;
        $Parameters = array();
        foreach($ModelTable->GetDataDefinition() as $Key => $Definition){
            switch($Definition["Type"]){
                case "Normal":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
                    $Data =  $Object->GetObjectData($Key);
                    if(is_bool($Data)){
                        if($Data==true)
                            $SQL .= $Key . "= TRUE";
                        else
                             $SQL .= $Key . "= FALSE";
                    }
                    elseif($Data!==null){
                        //$SQL .= $Key ." = "" . $Data. """;
                        $SQL .= $Key ." = '%s'";
                        array_push($Parameters, $Data);
                    }
                    else {
                        $SQL .= $Key ." = NULL";
                    }
                    $Count++;
                    break;
                case "ForeignKey":
                    if($Count>1){
                        // add , at the end
                        $SQL .= ", ";
                    }
                    $Data =  $Object->GetObjectData($Key);
                    if(is_bool($Data)){
                        if($Data==true)
                            $SQL .= $Key . "= TRUE";
                        else
                             $SQL .= $Key . "= FALSE";
                    }
                    elseif($Data!==null){
                        $SQL .= $Key ." = '" . $Data. "'";
                    }
                    else {
                        $SQL .= $Key ." = NULL";
                    }
                    $Count++;
                    break;
            }
        }
        //$SQL .= " WHERE ". $ModelTable->GetPrimaryKeyName() . " = "%s"";
        $SQL .= " ". self::CreateWhereStatementByPrimaryKey($ModelTable);
        array_push($Parameters, $Object->GetObjectData($ModelTable->GetPrimaryKeyName()));
        return array ("SQL" => $SQL, "Parameters" => $Parameters);
    }
   
    public static function CreateWhereStatementByPrimaryKeys(ModelTable $ModelTable, $Keys){
        $Result = self::CreateInStatementForKeys($ModelTable, $ModelTable->GetPrimaryKeyName(), $Keys);
        $Result['SQL'] = "WHERE " . $Result['SQL'];
        return $Result;
    }
    
    public static function CreateInStatementForKeys(ModelTable $ModelTable, $Field, $Keys){
        $SQL =  $ModelTable->GetTableName()  . "." . $Field . " IN ( ";
        $Count = 0;
        $Parameters = array();
        foreach($Keys as $Key){
            if($Count!=0){
                $SQL .=  ",";
            }
            $SQL .=  "'%s'";
            array_push($Parameters, $Key);
            $Count++;
        }
        $SQL .= ')';
        return array ('SQL' => $SQL, 'Parameters' => $Parameters);
    }

     public static function CreatePreloadStatement(ModelTable $ModelTable,ModelTable $ListModelTable, ModelArray $List, $Field){
        $DataDefinitions = $ListModelTable->GetDataDefinition(); 
        if(!isset($DataDefinitions[$Field])) {
            throw new Exception('The field must be a field from the data definition.');
        }
        $DataDefinition = $DataDefinitions[$Field];
        $SQL = 'WHERE ' . $ModelTable->GetTableName() . '.' . $ModelTable->GetPrimaryKeyName()  . ' IN (';
        if($DataDefinition['Type']=='ManyForeignObjects'){
            
            $Parameters = array();
            $Count = 0;
            foreach($List as $Item){
                if($Item!=null){
                    $Keys = $Item->GetObjectData($Field);
                    $KeysList = explode(',', $Keys);
                    foreach($KeysList as $Key){
                        if($Count!=0){
                            $SQL .=  ",";
                        }
                        $SQL .=  "'%s'";
                        array_push($Parameters, $Key);
                        $Count++;
                    }

                }
            }
            $SQL .= ')';
        }
        elseif($DataDefinition['Type']=='ForeignObject'){
            $Parameters = array();
            $Count = 0;
            foreach($List as $Item){
                if($Item!=null){
                    $Key = $Item->GetObjectData($DataDefinition['ForeignKey']);
                    if($Count!=0){
                        $SQL .=  ",";
                    }
                    $SQL .=  "'%s'";
                    array_push($Parameters, $Key);
                    $Count++;
                }
            }
            $SQL .= ')';
        }
        
        return array ('SQL' => $SQL, 'Parameters' => $Parameters);
    }
}

?>