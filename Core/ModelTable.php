<?php

class ModelTable {
    // -- static --
    protected static $ModelTables = array();

    public static function Get($Name){
        // instance already stored
        if(isset(self::$ModelTables[$Name])){
            return self::$ModelTables[$Name];
        }
        else {
            // create new instance
            $ClassName = $Name . 'ModelTable';
            if(class_exists($ClassName)){
                $Instance = new $ClassName();
                self::$ModelTables[$Name] = $Instance;
                return $Instance;
            }
            else {
                throw new Exception('Model table class for ' . $Name . ' not found.');
            }
        }
    }


    // -- INSTANCE --

    protected $DataDefinition;
    protected $ModelClassName;
    protected $TableName;
    protected $PrimaryKeyName;
    protected $Cache;

    public function __construct(){
        $this->Cache = array();
    }

    public function GetPrimaryKeyName(){
        if($this->PrimaryKeyName!=null){
            return $this->PrimaryKeyName;
        }
        else {
            // try to find primary key
            foreach($this->DataDefinition as $Key => $Value){
                if($Value['Type']=='PrimaryKey'){
                    // primary key found
                    $this->PrimaryKeyName = $Key;
                    return $this->PrimaryKeyName;
                }
            }
        }
        throw new Exception('No primary key found.');
    }

    public function GetByPrimaryKey($PrimaryKey){
        if($PrimaryKey==0){
            return null;
        }
        else{
            // search in cache
            $CacheInstance = $this->GetFromCacheByPrimaryKey($PrimaryKey);
            if($CacheInstance!=null){
                return $CacheInstance;
            }
            else {
                // get via sql
                $Parameters = array();
                array_push($Parameters, $PrimaryKey);
                $Conditions = 'WHERE ' . $this->GetTableName()  . '.' . $this->GetPrimaryKeyName() . ' = %s';
                return $this->SelectSingle($Conditions, '', $Parameters);
            }
        }
    }

    public function GetTableName(){
        return $this->TableName;
    }

    public function GetFromCacheByPrimaryKey($PrimaryKey){
        if(isset($this->Cache[$PrimaryKey])){
            return $this->Cache[$PrimaryKey]['Instance'];
        }
        return null;
    }

    public function StoreInCache(Model $Instance){
        if($Instance!=null){
            $PrimaryKey = $Instance->GetPrimaryKey();
            if($PrimaryKey!=''){
                // store in cache
                $this->Cache[$PrimaryKey] = array (
                    'Instance' => $Instance,
                    'Timestamp' => microtime()
                );
            }
        }
    }

    public function GetDataDefinition(){
        return $this->DataDefinition;
    }

    public function CreateSelect($Conditions,$OrderBy){
        $SQL = '';
        $SQL .= $this->CreateSelectPart();
        $SQL .= ' ';
        $SQL .= $Conditions;
        $SQL .= ' ';
        $SQL .= $this->CreateGroupByPart();
        $SQL .= ' ';
        $SQL .= $OrderBy;
        return $SQL;
    }

    public function SelectList($Conditions,  $OrderBy, $Parameters = array()){
        $QueryString = $this->CreateSelect($Conditions, $OrderBy);
        $Result = MySQLManager::SelectWithParameters($QueryString, $Parameters);
        $List = array();
        while ($Data = mysql_fetch_assoc($Result)) {
            $Classname = $this->ModelClassName . 'Model';
            $Model = new $Classname();
            $Model->Fill($Data);
            array_push($List, $Model);
        }
        return $List;
    }

    public function SelectSingle($Conditions, $OrderBy, $Parameters = array()){
        $List = $this->SelectList($Conditions, $OrderBy, $Parameters);
        if(count($List)>0){
            return $List[0];
        }
        else {
            return null;
        }
    }

    public function Insert(Model $Object){
        $PrimaryKey = $Object->GetPrimaryKey();
        If(empty($PrimaryKey)){
            // insert into database
            $Insert = $this->CreateInsertPart($Object);
            $PrimaryKey = MySQLManager::InsertWithParameters($Insert['SQL'],$Insert['Parameters']);
            // update primarykey in object
            $Object->UpdateObjectData($this->GetPrimaryKeyName(),$PrimaryKey);

            // update Foreign Objects that are in cache
            $DataDefinition = $this->GetDataDefinition();
            foreach($DataDefinition as $Key => $Defintion){
                if($Defintion['Type']=='ForeignKey'){
                    $ForeignKey = $Object->GetObjectData($Key);
                    if(isset($ForeignKey)&&$ForeignKey!=0){
                        $ForeignModelTable = ModelTable::Get($Defintion['ModelTable']);

                        // find data field from type ManyForeignObjects
                        foreach($ForeignModelTable->GetDataDefinition() as $ForeignDefinitionKey => $ForeignDefinition){
                            if($ForeignDefinition['Type']=='ManyForeignObjects'&&$ForeignDefinition['ForeignKey']==$Key){
                                $ForeignObject = $ForeignModelTable->GetFromCacheByPrimaryKey($ForeignKey);
                                if($ForeignObject!=null){
                                    // add primary key from inserted object to list
                                    $Old = $ForeignObject->GetObjectData($ForeignDefinitionKey);
                                    $New = $Old . ',' . $PrimaryKey;
                                    $ForeignObject->UpdateObjectData($ForeignDefinitionKey, $New);
                                }
                                break;
                            }
                        }
                    }
                }
            }
            $this->StoreInCache($Object);
            return $PrimaryKey;
        }
        else {
           throw new Exception('The primarykey of this object is already set and the object can\'t be inserted.');
        }
    }

    public function Update(Model $Object){
        $PrimaryKey = $Object->GetPrimaryKey();
        if(!empty($PrimaryKey)){
                // create update statement
                $Update = $this->CreateUpdatePart($Object);
                return MySQLManager::UpdateWithParameters($Update['SQL'], $Update['Parameters']);


        }
        else {
            throw new Exception('Primary key isn\'t set, can\'t update');
        }
    }

    public function CreateUpdatePart(Model $Object){
        $SQL = 'UPDATE '. $this->GetTableName() . ' SET ';
        $Count = 1;
        $Parameters = array();
        foreach($this->GetDataDefinition() as $Key => $Definition){
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
        $SQL .= ' WHERE '. $this->GetPrimaryKeyName() . ' = %s';
        array_push($Parameters, $Object->GetObjectData($this->GetPrimaryKeyName()));
        return array ('SQL' => $SQL, 'Parameters' => $Parameters);
    }

    public function CreateInsertPart(Model $Object){
        $SQL = 'INSERT INTO ' . $this->GetTableName();
        $DataDefinition = $this->GetDataDefinition();
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
            }
        }
        $SQL .= ')';
        return array ('SQL' => $SQL, 'Parameters' => $Parameters);
    }

    public function SQLAttribute($Attribute, $Alias = '', $Table = ''){
        if($Table==''){
            $Table = $this->GetTableName();
        }
        $SQL = $Table . '.'. $Attribute;
        if($Alias!=''){
            $SQL .= ' as '. $Alias;
        }
        return $SQL;
    }

    public function CreateSelectPart(){
        $SQL = 'SELECT ';
        $Count = 1;
        $Join = false;
        $AliasArray = array();
        foreach ($this->GetDataDefinition() as $Key => $Definition) {
            switch ($Definition['Type']) {
                case 'Normal':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .= $this->SQLAttribute($Key);
                    $Count++;
                    break;
                case 'PrimaryKey':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .= $this->SQLAttribute($Key);
                    $Count++;
                    break;
                case 'ForeignKey':
                    if($Count>1){
                        // add , at the end
                        $SQL .= ', ';
                    }
                    $SQL .= $this->SQLAttribute($Key);
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
                    $ModelTable =  ModelTable::Get($Definition['ModelTable']);
                    // generate group_conact
                    $SQL.= 'GROUP_CONCAT(' . $this->SQLAttribute($ModelTable->GetPrimaryKeyName(), '',$Alias  ) .') as '. $Key;
                    $Join = true;
                    $Count++;
                    break;
            }
            
        }
        $SQL .=  ' FROM ' . $this->GetTableName() ;


        if($Join){
            // add joins
            foreach ($this->GetDataDefinition() as $Key => $Definition) {
                 if ($Definition['Type']=='ManyForeignObjects') {
                     $ModelTable =  ModelTable::Get($Definition['ModelTable']);
                         $SQL .= ' LEFT JOIN ' . $ModelTable->GetTableName() . ' as ' . $AliasArray[$Key]
                                . ' ON ' . $this->SQLAttribute($Definition['ForeignKey'],'',$AliasArray[$Key])
                                . ' = ' . $this->SQLAttribute($this->GetPrimaryKeyName(),'',$this->GetTableName());
    

                 }
            }
        }
        return $SQL;
    }

    public function CreateGroupByPart(){
        $Count = 1;
        $SQL = '';
        foreach ($this->GetDataDefinition() as $Key => $Definition) {
             if ($Definition['Type']=='ManyForeignObjects') {
                if($Count>1){
                     // add , at the end
                    $SQL .= ', ';
                }
                $SQL .=  $this->SQLAttribute($this->GetPrimaryKeyName());
                $Count++;
             }
        }
        // if we have a group by part
        if($Count>1){
            $SQL = 'GROUP BY '. $SQL;
        }
        return $SQL;
    }

    public function Delete(Model $Object){
        $PrimaryKey = $Object->GetPrimaryKey();
        if($PrimaryKey!=null){
            $QueryString = $this->CreateDeletePart();
            $Parameters = array();
            array_Push($Parameters, $PrimaryKey);
            MySQLManager::DeleteWithParameters($QueryString, $Parameters);

            // update foreign objects
            foreach($this->GetDataDefinition() as $DefinitionKey => $Definition){
                if($Definition['Type']=='ForeignKey'){
                    $ForeignModelTable = ModelTable::Get($Definition['ModelTable']);
                    $ForeignKey = $Object->GetObjectData($DefinitionKey);
                    $ForeignObject = $ForeignModelTable->GetFromCacheByPrimaryKey($ForeignKey);
                    // if object exist in cache and needs to be updated
                    if($ForeignObject!=null){
                        foreach($ForeignModelTable->GetDataDefinition() as $ForeignDefinitionKey => $ForeignDefinition){
                            if($ForeignDefinition['Type']=='ManyForeignObjects'&&$ForeignDefinition['ForeignKey']==$DefinitionKey){
                                $OldKeys = $ForeignObject->GetObjectData($ForeignDefinitionKey);
                                // remove foreign key from key list
                                $ForeignObject->UpdateObjectData($ForeignDefinitionKey, str_replace($ForeignKey, '', $OldKeys));
                                break;
                            }
                        }
                    }
                }
            }
        }
        else {
            throw new Exception('Can\'t delete model cause it has no primary key.');
        }
    }

    public function CreateDeletePart(){
        return 'DELETE FROM '. $this->GetTableName() . ' WHERE '. $this->GetPrimaryKeyName() . ' = %s';
    }
}

?>
