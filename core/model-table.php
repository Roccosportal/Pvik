<?php

class ModelTable {
    // -- static --
    /**
     * Contains the instances of loaded ModelTables
     * @var array 
     */
    protected static $ModelTables = array();
    /**
     * Gets a ModelTable instance or creates a new one if a instance doesn't exsists yet.
     * @return ModelTable
     */
    public static function Get($ModelTableName){
        if(!is_string($ModelTableName)){
            throw new Exception('ModelTableName must be a string.');
        }
        // instance already stored
        if(isset(self::$ModelTables[$ModelTableName])){
            return self::$ModelTables[$ModelTableName];
        }
        else {
            // create new instance
            if(self::Exists($ModelTableName)){
                $ClassName = $ModelTableName . 'ModelTable';
                $Instance = new $ClassName();
                self::$ModelTables[$ModelTableName] = $Instance;
                return $Instance;
            }
            else {
                throw new Exception('Model table class for ' . $ModelTableName . ' not found.');
            }
        }
    }
    
    /**
     * Checks if the class for a model table exists.
     * @param string $ModelTableName
     * @return bool 
     */
    public static function Exists($ModelTableName){
        $ClassName = $ModelTableName . 'ModelTable';
        if(class_exists($ClassName)){
            return true;
        }
        else {
            return false;
        }
    }


    // -- INSTANCE --
    /**
     * Contains the field definition array.
     * Filled in a child class.
     * @var array 
     */
    protected $FieldDefinition;
    /**
     * Contains the real table name.
     * Filled in a child class.
     * @var string 
     */
    protected $TableName;
    /**
     * Contains the name of the primary key.
     * Whether filled in a chilled class or filled after running the method GetPrimaryKeyName().
     * @var string 
     */
    protected $PrimaryKeyName;
    /**
     * Contains the already loaded models.
     * @var array 
     */
    protected $Cache;
    /**
     * Contains the name of the ModelTable.
     * @var type 
     */
    protected $ModelTableName;
    
    /**
     * A instance of the field definition helper.
     * @var FieldDefinitionHelper 
     */
    protected $FieldDefinitionHelper;
    /**
     * Contains the name of the Model that belongs to this ModelTable.
     * Filled in a child class.
     * @var string 
     */
    protected $ModelName;
    /**
     * Contains a value that indicates if all Models are loaded.
     * @var bool 
     */
    protected $ChacheLoadedAll;
    /**
     * Contains the ModelArray of all Models if all are loaded.
     * @var ModelArray
     */
    protected $ChacheModelArrayAll;

    /**
     * 
     */
    public function __construct(){
        $this->Cache = array();
        $this->LoadedAll = false;
    }
    
    /**
     * Returns the PrimaryKey name.
     * @return string 
     */
    public function GetPrimaryKeyName(){
        if($this->PrimaryKeyName!=null){
            return $this->PrimaryKeyName;
        }
        else {
            $Helper = $this->GetFieldDefinitionHelper();
            // try to find primary key
            foreach($Helper->GetFieldList() as $FieldName){
                if($Helper->IsTypePrimaryKey($FieldName)){
                    // primary key found
                    $this->PrimaryKeyName = $Key;
                    return $this->PrimaryKeyName;
                }
            }
        }
        throw new Exception('No primary key found.');
    }
    
    /**
     * Returns the real table name.
     * @return string 
     */
    public function GetTableName(){
        return $this->TableName;
    }
    
    /**
     * Returns the name of the Model that belongs to this ModelTable.
     * @return type 
     */
    public function GetModelName(){
        if(empty($this->ModelName)){
            throw new Exception('ModelName not set up for '. get_class($this));
        }
        return $this->ModelName;
    }
    
    /**
     * Returns the name of the Model class that belongs to this ModelTable.
     * @return string 
     */
    public function GetModelClassName(){
        $ModelClassName = $this->GetModelName() .'Model';
        return $ModelClassName;
    }
    
    /**
     * Returns the name of the ModelTable withouth the suffix ModelTable.
     * @return string 
     */
    public function GetModelTableName(){
        if($this->ModelTableName==null){
            $this->ModelTableName = str_replace('ModelTable', '', get_class());
        }
        return $this->ModelTableName;
    }
    
    /**
     * Stores a instance of model into the cache.
     * @param Model $Instance 
     */
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

    /**
     * Returns a instance of a field definition helper or creates a new one.
     * @return FieldDefinitionHelper 
     */
    public function GetFieldDefinitionHelper(){
        // just use one instance
        if($this->FieldDefinitionHelper==null){
            $this->FieldDefinitionHelper = new FieldDefinitionHelper($this->FieldDefinition, $this);
        }
        return $this->FieldDefinitionHelper;
    }


    /**
     * Select a ModelArray from the database.
     * @param Query $Query
     * @return ModelArray 
     */
    public function Select(Query $Query){
        $QueryString = SQLBuilder::CreateSelectStatement($this, $Query->GetConditions(), $Query->GetOrderBy());
        $ModelArray = SQLManager::FillList($this, $QueryString, $Query->GetParameters());
        // if the query don't have any conditions we have loaded all objects and can save the complete list 
        // in chache
        if($Query->GetConditions()==''){
            $this->ChacheModelArrayAll = $ModelArray;
            $this->ChacheLoadedAll = true;
        }
        return $ModelArray;
    }
    
    
    /**
     * Select a single Model from the database.
     * @param Query $Query
     * @return Model 
     */
   public function SelectSingle(Query $Query){
        $List = $this->Select($Query);
        if($List->count()>0){
            // return first element
            return $List[0];
        }
        else {
            return null;
        }
    }

    /**
     * Select all Models from the database.
     * @return ModelArray 
     */
    public function SelectAll(){
       // creating a new query without any conditions
       $Query = new Query($this->GetModelTableName());
       return $this->Select($Query);
    }
    

    /**
     * Insert a Model to the database and updates the cache.
     * Returns the primary key.
     * @param Model $Object
     * @return string 
     */
    public function Insert(Model $Object){
        $PrimaryKey = $Object->GetPrimaryKey();
        If(empty($PrimaryKey)){
            // insert into database
            $Insert = SQLBuilder::CreateInsertStatement($this, $Object);
            $PrimaryKey = SQLManager::InsertWithParameters($Insert['SQL'],$Insert['Parameters']);
            // update primarykey in object
            $Object->SetFieldData($this->GetPrimaryKeyName(),$PrimaryKey);

            // update Foreign Objects that are in cache
            $Helper = $this->GetFieldDefinitionHelper();
            foreach($Helper->GetFieldList() as $FieldName){
                if($Helper->IsTypeForeignKey($FieldName)){
                    $ForeignKey = $Object->GetFieldData($FieldName);
                    if(isset($ForeignKey)&&$ForeignKey!=0){
                        
                        $ForeignModelTable = $Helper->GetModelTable($FieldName);
                        $ForeignHelper = $ForeignModelTable->GetFieldDefinitionHelper();
                        // find data field from type ManyForeignObjects that have a reference to this model table
                        foreach($ForeignHelper->GetManyForeignObjectsFieldList() as $ForeignFieldName){
                            if($ForeignHelper->GetModelTableName($ForeignFieldName)==$this->GetModelTableName()
                                    && $ForeignHelper->GetForeignKeyFieldName($ForeignFieldName)==$FieldName){
                                
                                $ForeignObject = $ForeignModelTable->LoadFromCacheByPrimaryKey($ForeignKey);
                                if($ForeignObject!=null){
                                    // add primary key from inserted object to list
                                    $Old = $ForeignObject->GetFieldData($ForeignDefinitionKey);
                                    $New = $Old . ',' . $PrimaryKey;
                                    $ForeignObject->SetFieldData($ForeignDefinitionKey, $New);
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

    /**
     * Updates a Model on the database.
     * @param Model $Object
     * @return mixed 
     */
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

    /**
     * Deletes a Model from the database and updates the cache.
     * @param Model $Object 
     */
    public function Delete(Model $Object){
        $PrimaryKey = $Object->GetPrimaryKey();
        if($PrimaryKey!=null){
            $QueryString = SQLBuilder::CreateDeleteStatement($this);
            $Parameters = array();
            array_Push($Parameters, $PrimaryKey);
            SQLManager::DeleteWithParameters($QueryString, $Parameters);

            // update foreign objects
            $Helper = $this->GetFieldDefinitionHelper();
            
            foreach($Helper->GetFieldList() as $FieldName){
                if($Helper->IsTypeForeignKey($FieldName)){
                    $ForeignModelTable =  $Helper->GetModelTable($FieldName);
                    $ForeignKey = $Object->GetFieldData($FieldName);
                    $ForeignObject = $ForeignModelTable->LoadFromCacheByPrimaryKey($ForeignKey);
                    // if object exist in cache and needs to be updated
                    if($ForeignObject!=null){
                         // look through foreign model
                        $ForeignHelper = $ForeignModelTable->GetFieldDefinitionHelper();
                        foreach($ForeignHelper->GetManyForeignObjectsFieldList() as $ForeignModelTableFieldName){
                            // searching for a ManyForeignObjects field with ForeignKey reference to this field
                            if($ForeignHelper->GetModelTableName($ForeignModelTableFieldName) == $this->GetModelTableName()
                                    && $ForeignHelper->GetForeignKeyFieldName($ForeignModelTableFieldName) == $FieldName){
                                $OldKeys = $OldForeignObject->GetFieldData($ForeignModelTableFieldName);
                                // delete from old keys
                                $OldForeignObject->SetObjectData($ForeignModelTableFieldName, str_replace($PrimaryKey, '', $OldKeys));
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
    
    /**
     * Select a ModelArray from database by primary keys.
     * @param array $PrimaryKeys
     * @return ModelArray 
     */
    public function SelectByPrimaryKeys(array $PrimaryKeys){
        $Values =  SQLBuilder::CreateWhereStatementByPrimaryKeys($this, $PrimaryKeys);
        $Query = new Query($this->GetModelTableName());
        $Query->SetConditions($Values['SQL']);
        foreach($Values['Parameters'] as $Parameter){
            $Query->AddParameter($Parameter);
        }
        return $this->Select($Query);
    }  
    
    /**
     * Select a ModelArray from database by foreign keys through a sql IN statement.
     * Example: SELECT * FROM Books WHERE Books.AuthorID IN ('1', '2', '3')
     * @param string $FieldName
     * @param array $Keys
     * @return ModelArray 
     */
    public function SelectByForeignKeys($FieldName,array $Keys){
        $InStatementResult = SQLBuilder::CreateInStatementForKeys($this, $FieldName, $Keys);
        $Query = new Query($this->GetModelTableName());
        $Query->SetConditions("WHERE " . $InStatementResult['SQL'] . " ");
        foreach($InStatementResult['Parameters'] as $Parameter){
            $Query->AddParameter($Parameter);
        }
        return $this->Select($Query);
    }
    
    /**
     * Select a Model from database by its primary key.
     * @param string $PrimaryKey
     * @return Model 
     */
    public function SelectByPrimaryKey($PrimaryKey){
        $Conditions = SQLBuilder::CreateWhereStatementByPrimaryKey($this);
        $Query = new Query($this->GetModelTableName());
        $Query->SetConditions($Conditions);
        $Query->AddParameter($PrimaryKey);
        return $this->SelectSingle($Query);
    }
    
    /**
     * Loads a Model from cache by its primary key.
     * @param string $PrimaryKey
     * @return Model 
     */
    public function LoadFromCacheByPrimaryKey($PrimaryKey){
        if(!is_string($PrimaryKey)&&!is_int($PrimaryKey)){
            throw new Exception('primary key must be a string or int');
        }
        if(isset($this->Cache[$PrimaryKey])){
            return $this->Cache[$PrimaryKey]['Instance'];
        }
        return null;
    }
    
    /**
     * Returns a Model from cache or from database by its primary key.
     * @param string $PrimaryKey
     * @return Model 
     */
    public function LoadByPrimaryKey($PrimaryKey){
        if(!is_string($PrimaryKey)&&!is_int($PrimaryKey)){
            throw new Exception('primary key must be a string or int');
        }
        if($PrimaryKey===0||$PrimaryKey===null||$PrimaryKey===''){
            return null;
        }
        else{
            // search in cache
            $CacheInstance = $this->LoadFromCacheByPrimaryKey($PrimaryKey);
            if($CacheInstance!=null){
                return $CacheInstance;
            }
            else {
                // get via select
                return $this->SelectByPrimaryKey($PrimaryKey);
            }
        }
    }
    
    /**
     * Returns a ModelArray from cache or database by the primary keys.
     * @param array $Keys
     * @return ModelArray 
     */
    public function LoadByPrimaryKeys(array $Keys){
        // convert to array
        $List = new ModelArray();
        $List->SetModelTable($this);
        $LoadKeys = array();
        foreach($Keys as $Key){
            if(!empty($Key)){
                // search in cache
                $Item = $this->LoadFromCacheByPrimaryKey($Key);
                // mark for loading later
                if($Item==null){
                    array_push($LoadKeys, $Key);
                }
                else {
                    $List->append($Item);
                }
            }
        }
        if(!empty($LoadKeys)){
            // now load every data we didn't find in the cache
            $LoadedItems = $this->SelectByPrimaryKeys($LoadKeys);
            foreach($LoadedItems as $LoadedItem){
                 $List->append($LoadedItem);
            }
        }
        return $List;
    }
    
    
    /**
     *  Returns all Models from cache or from database.
     *  @return ModelArray
     */
    public function LoadAll(){
        if(!$this->ChacheLoadedAll) {
            $this->SelectAll();
        }
        return $this->ChacheModelArrayAll;
    }
    
}

?>