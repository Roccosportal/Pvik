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
        if($PrimaryKey===0||$PrimaryKey===null||$PrimaryKey===''){
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
                $Conditions = SQLBuilder::CreateWhereStatementByPrimaryKey($this);
                return $this->SelectSingle($Conditions, '', $Parameters);
            }
        }
    }

    public function GetTableName(){
        return $this->TableName;
    }
    
    public function GetModelClassName(){
        return $this->ModelClassName;
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

    public function SelectList($Conditions,  $OrderBy, $Parameters = array()){
        $QueryString = SQLBuilder::CreateSelectStatement($this,$Conditions, $OrderBy);
        return SQLManager::FillList($this, $QueryString, $Parameters);
    }
    
    public function SelectAll(){
        return $this->SelectList('', '');
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
            $Insert = SQLBuilder::CreateInsertStatement($this, $Object);
            $PrimaryKey = SQLManager::InsertWithParameters($Insert['SQL'],$Insert['Parameters']);
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
                $Update = SQLBuilder::CreateUpdateStatement($this, $Object);
                return SQLManager::UpdateWithParameters($Update['SQL'], $Update['Parameters']);


        }
        else {
            throw new Exception('Primary key isn\'t set, can\'t update');
        }
    }

    public function Delete(Model $Object){
        $PrimaryKey = $Object->GetPrimaryKey();
        if($PrimaryKey!=null){
            $QueryString = SQLBuilder::CreateDeleteStatement($this);
            $Parameters = array();
            array_Push($Parameters, $PrimaryKey);
            SQLManager::DeleteWithParameters($QueryString, $Parameters);

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
    public function ByPrimaryKeys($PrimaryKeys){
        $Values =  SQLBuilder::CreateWhereStatementByPrimaryKeys($this, $PrimaryKeys);
        return $this->SelectList($Values['SQL'], '', $Values['Parameters']);
    }
    
    public function Preload(ModelArray $List, $Field){
        $ListModelTable = null;
        foreach($List as $Item){
            if($Item!=null){
                $ListModelTable = $Item->GetModelTable();
                break;
            }
        } 
        $Values = SQLBuilder::CreatePreloadStatement($this,$ListModelTable, $List, $Field);
        if(count($Values['Parameters'])!= null){
            return $this->SelectList($Values['SQL'], '', $Values['Parameters']);
        }
        else {
            return null;
        }
    }
    
    public function SelectListByForeignKeys($Field, $Keys){
        $InStatementResult = SQLBuilder::CreateInStatementForKeys($this, $Field, $Keys);
        $WhereStatement = "WHERE " . $InStatementResult['SQL'] . " ";
        $Parameters = $InStatementResult['Parameters'];
        return $this->SelectList($WhereStatement, '', $Parameters);
    }

}

?>