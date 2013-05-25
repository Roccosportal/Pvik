<?php
namespace Pvik\Database\ORM;
use Pvik\Core\Config;
use Pvik\Database\SQL\Manager;

/**
 * Represents a database table
 */
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
    public static function Get($ModelTableName) {
        if (!is_string($ModelTableName)) {
            throw new \Exception('ModelTableName must be a string.');
        }
        // instance already stored
        if (isset(self::$ModelTables[$ModelTableName])) {
            return self::$ModelTables[$ModelTableName];
        } else {
            // create new instance

            $ClassName = $ModelTableName;
            if ($ClassName[0] !== '\\') {
                $ClassName = Config::$Config['DefaultNamespace'] . Config::$Config['DefaultNamespaceModelTable'] . '\\' . $ClassName;
            }

            $Instance = new $ClassName();
            self::$ModelTables[$ModelTableName] = $Instance;
            return $Instance;
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
     *  Contains the cache class
     * @var CacheModelTable 
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
    protected $EntityName;

    /**
     *
     * @return Pvik\Database\Cache\ModelTable
     */
    public function GetCache() {
        if ($this->Cache === null) {
            $this->Cache = new \Pvik\Database\Cache\ModelTable($this);
        }
        return $this->Cache;
    }

    /**
     * Returns the PrimaryKey name.
     * @return string 
     */
    public function GetPrimaryKeyName() {
        if ($this->PrimaryKeyName != null) {
            return $this->PrimaryKeyName;
        } else {
            $Helper = $this->GetFieldDefinitionHelper();
            // try to find primary key
            foreach ($Helper->GetFieldList() as $FieldName) {
                if ($Helper->IsTypePrimaryKey($FieldName)) {
                    // primary key found
                    $this->PrimaryKeyName = $Key;
                    return $this->PrimaryKeyName;
                }
            }
        }
        throw new \Exception('No primary key found.');
    }

    /**
     * Returns the real table name.
     * @return string 
     */
    public function GetTableName() {
        return $this->TableName;
    }

    /**
     * Returns the name of the Model that belongs to this ModelTable.
     * @return string 
     */
    public function GetEntityName() {
        if (empty($this->EntityName)) {
            throw new \Exception('EntityName not set up for ' . get_class($this));
        }
        return $this->EntityName;
    }

    /**
     * Returns the name of the Model class that belongs to this ModelTable.
     * @return string 
     */
    public function GetEntityClassName() {
        $EntityClassName = $this->GetEntityName();
        if ($EntityClassName[0] !== '\\') {
            $EntityClassName = Config::$Config['DefaultNamespace'] . Config::$Config['DefaultNamespaceEntity'] . '\\' . $EntityClassName;
        }
        return $EntityClassName;
    }

    /**
     * Returns the name of the ModelTable withouth the suffix ModelTable.
     * @return string 
     */
    public function GetModelTableName() {
        if ($this->ModelTableName == null) {
            $Class = explode('\\', get_class($this));
            $this->ModelTableName = end($Class);
        }
        return $this->ModelTableName;
    }

    /**
     * Returns a instance of a field definition helper or creates a new one.
     * @return FieldDefinitionHelper 
     */
    public function GetFieldDefinitionHelper() {
        // just use one instance
        if ($this->FieldDefinitionHelper == null) {
            $this->FieldDefinitionHelper = new FieldDefinitionHelper($this->FieldDefinition, $this);
        }
        return $this->FieldDefinitionHelper;
    }

    /**
     * Select all Models from the database.
     * 
     * @return EntityArray 
     */
    public function SelectAll() {
        // creating a new query without any conditions
        $queryBuilder = $this->getEmptyQueryBuilder();
        return $queryBuilder->select();
    }
    
    public function getEmptySelectBuilder(){
        return Query\Builder\Select::getEmptyInstance($this->GetModelTableName());
    }

    /**
     * Select a EntityArray from database by primary keys.
     * @param array $PrimaryKeys
     * @return EntityArray 
     */
    public function SelectByPrimaryKeys(array $PrimaryKeys) {
        $queryBuilder = $this->getEmptySelectBuilder();
        $queryBuilder->where($this->GetTableName() . '.' .$this->GetPrimaryKeyName(). ' IN (%s)');
        $queryBuilder->addParameter($PrimaryKeys);
        return $queryBuilder->select();
    }

    /**
     * Select a Entity from database by its primary key.
     * @param string $PrimaryKey
     * @return Entity 
     */
    public function SelectByPrimaryKey($PrimaryKey) {
        $queryBuilder = $this->getEmptySelectBuilder();
        $queryBuilder->where($this->GetTableName() . '.' . $this->GetPrimaryKeyName() . ' = %s');
        $queryBuilder->addParameter($PrimaryKey);
        return $queryBuilder->selectSingle();
    }

    /**
     * Returns a Entity from cache or from database by its primary key.
     * @param string $PrimaryKey
     * @return Entity 
     */
    public function LoadByPrimaryKey($PrimaryKey) {
        if (!is_string($PrimaryKey) && !is_int($PrimaryKey)) {
            throw new \Exception('primary key must be a string or int');
        }
        if ($PrimaryKey === 0 || $PrimaryKey === null || $PrimaryKey === '') {
            return null;
        } else {
            // search in cache
            $CacheInstance = $this->GetCache()->LoadByPrimaryKey($PrimaryKey);
            if ($CacheInstance != null) {
                return $CacheInstance;
            } else {
                // get via select
                return $this->SelectByPrimaryKey($PrimaryKey);
            }
        }
    }

    /**
     * Returns a EntityArray from cache or database by the primary keys.
     * @param array $Keys
     * @return EntityArray 
     */
    public function LoadByPrimaryKeys(array $Keys) {
        // convert to array
        $List = new EntityArray();
        $List->SetModelTable($this);
        $LoadKeys = array();
        foreach ($Keys as $Key) {
            if (!empty($Key)) {
                // search in cache
                $Item = $this->GetCache()->LoadByPrimaryKey($Key);
                // mark for loading later
                if ($Item == null) {
                    array_push($LoadKeys, $Key);
                } else {
                    $List->append($Item);
                }
            }
        }
        if (!empty($LoadKeys)) {
            // now load every data we didn't find in the cache
            $LoadedItems = $this->SelectByPrimaryKeys($LoadKeys);
            foreach ($LoadedItems as $LoadedItem) {
                $List->append($LoadedItem);
            }
        }
        return $List;
    }

    /**
     *  Returns all Entities from cache or from database.
     *  @return EntityArray
     */
    public function LoadAll() {
        if (!$this->GetCache()->IsLoadedAll()) {
            return $this->SelectAll();
        }
        return $this->GetCache()->GetEntityArrayAll();
    }
    
    
    /**
     * Creates a EntityArray from a select statemet result
     * @param array $Parameters
     * @return \Pvik\Database\ORM\EntityArray 
     */
    public function FillEntityArray($result) {
        $List = new \Pvik\Database\ORM\EntityArray();
        $List->SetModelTable($this);
        while ($Data = Manager::GetInstance()->FetchAssoc($result)) {
            $Classname = $this->GetEntityClassName();
            $Model = new $Classname();
            $Model->Fill($Data);
            $List->append($Model);
        }
        return $List;
    }
}