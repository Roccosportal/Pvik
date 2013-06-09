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
    protected static $modelTables = array();

    /**
     * Gets a ModelTable instance or creates a new one if a instance doesn't exsists yet.
     * @return ModelTable
     */
    public static function get($modelTableName) {
        if (!is_string($modelTableName)) {
            throw new \Exception('ModelTableName must be a string.');
        }
        // instance already stored
        if (isset(self::$modelTables[$modelTableName])) {
            return self::$modelTables[$modelTableName];
        } else {
            // create new instance

            $className = $modelTableName;
            if ($className[0] !== '\\') {
                $className = Config::$config['DefaultNamespace'] . Config::$config['DefaultNamespaceModelTable'] . '\\' . $className;
            }

            $instance = new $className();
            self::$modelTables[$modelTableName] = $instance;
            return $instance;
        }
    }

    // -- INSTANCE --
    /**
     * Contains the field definition array.
     * Filled in a child class.
     * @var array 
     */
    protected $fieldDefinition;

    /**
     * Contains the real table name.
     * Filled in a child class.
     * @var string 
     */
    protected $tableName;

    /**
     * Contains the name of the primary key.
     * Whether filled in a chilled class or filled after running the method GetPrimaryKeyName().
     * @var string 
     */
    protected $primaryKeyName;

    /**
     *  Contains the cache class
     * @var CacheModelTable 
     */
    protected $cache;

    /**
     * Contains the name of the ModelTable.
     * @var type 
     */
    protected $modelTableName;

    /**
     * A instance of the field definition helper.
     * @var FieldDefinitionHelper 
     */
    protected $fieldDefinitionHelper;

    /**
     * Contains the name of the Model that belongs to this ModelTable.
     * Filled in a child class.
     * @var string 
     */
    protected $entityName;

    /**
     *
     * @return Pvik\Database\Cache\ModelTable
     */
    public function getCache() {
        if ($this->cache === null) {
            $this->cache = new \Pvik\Database\Cache\ModelTable($this);
        }
        return $this->cache;
    }

    /**
     * Returns the PrimaryKey name.
     * @return string 
     */
    public function getPrimaryKeyName() {
        if ($this->primaryKeyName != null) {
            return $this->primaryKeyName;
        } else {
            $helper = $this->getFieldDefinitionHelper();
            // try to find primary key
            foreach ($helper->getFieldList() as $fieldName) {
                if ($helper->isTypePrimaryKey($fieldName)) {
                    // primary key found
                    $this->primaryKeyName = $key;
                    return $this->primaryKeyName;
                }
            }
        }
        throw new \Exception('No primary key found.');
    }

    /**
     * Returns the real table name.
     * @return string 
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     * Returns the name of the Model that belongs to this ModelTable.
     * @return string 
     */
    public function getEntityName() {
        if (empty($this->entityName)) {
            throw new \Exception('EntityName not set up for ' . get_class($this));
        }
        return $this->entityName;
    }

    /**
     * Returns the name of the Model class that belongs to this ModelTable.
     * @return string 
     */
    public function getEntityClassName() {
        $entityClassName = $this->getEntityName();
        if ($entityClassName[0] !== '\\') {
            $entityClassName = Config::$config['DefaultNamespace'] . Config::$config['DefaultNamespaceEntity'] . '\\' . $entityClassName;
        }
        return $entityClassName;
    }

    /**
     * Returns the name of the ModelTable withouth the suffix ModelTable.
     * @return string 
     */
    public function getModelTableName() {
        if ($this->modelTableName == null) {
            $class = explode('\\', get_class($this));
            $this->modelTableName = end($class);
        }
        return $this->modelTableName;
    }

    /**
     * Returns a instance of a field definition helper or creates a new one.
     * @return FieldDefinitionHelper 
     */
    public function getFieldDefinitionHelper() {
        // just use one instance
        if ($this->fieldDefinitionHelper == null) {
            $this->fieldDefinitionHelper = new FieldDefinition\Helper($this->fieldDefinition, $this);
        }
        return $this->fieldDefinitionHelper;
    }

    /**
     * Select all Models from the database.
     * 
     * @return EntityArray 
     */
    public function selectAll() {
        // creating a new query without any conditions
        $queryBuilder = $this->getEmptySelectBuilder();
        return $queryBuilder->select();
    }
    /**
     * 
     * @return Query\Builder\Select
     */
    public function getEmptySelectBuilder(){
        return Query\Builder\Select::getEmptyInstance($this->getModelTableName());
    }

    /**
     * Select a EntityArray from database by primary keys.
     * @param array $primaryKeys
     * @return EntityArray 
     */
    public function selectByPrimaryKeys(array $primaryKeys) {
        $queryBuilder = $this->getEmptySelectBuilder();
        $queryBuilder->where($this->getTableName() . '.' .$this->getPrimaryKeyName(). ' IN (%s)');
        $queryBuilder->addParameter($primaryKeys);
        return $queryBuilder->select();
    }

    /**
     * Select a Entity from database by its primary key.
     * @param string $primaryKey
     * @return Entity 
     */
    public function selectByPrimaryKey($primaryKey) {
        $queryBuilder = $this->getEmptySelectBuilder();
        $queryBuilder->where($this->getTableName() . '.' . $this->getPrimaryKeyName() . ' = %s');
        $queryBuilder->addParameter($primaryKey);
        return $queryBuilder->selectSingle();
    }

    /**
     * Returns a Entity from cache or from database by its primary key.
     * @param string $primaryKey
     * @return Entity 
     */
    public function loadByPrimaryKey($primaryKey) {
        if (!is_string($primaryKey) && !is_int($primaryKey)) {
            throw new \Exception('primary key must be a string or int');
        }
        if ($primaryKey === 0 || $primaryKey === null || $primaryKey === '') {
            return null;
        } else {
            // search in cache
            $cacheInstance = $this->getCache()->loadByPrimaryKey($primaryKey);
            if ($cacheInstance != null) {
                return $cacheInstance;
            } else {
                // get via select
                return $this->selectByPrimaryKey($primaryKey);
            }
        }
    }

    /**
     * Returns a EntityArray from cache or database by the primary keys.
     * @param array $keys
     * @return EntityArray 
     */
    public function loadByPrimaryKeys(array $keys) {
        // convert to array
        $list = new EntityArray();
        $list->setModelTable($this);
        $loadKeys = array();
        foreach ($keys as $key) {
            if (!empty($key)) {
                // search in cache
                $item = $this->getCache()->loadByPrimaryKey($key);
                // mark for loading later
                if ($item == null) {
                    array_push($loadKeys, $key);
                } else {
                    $list->append($item);
                }
            }
        }
        if (!empty($loadKeys)) {
            // now load every data we didn't find in the cache
            $loadedItems = $this->selectByPrimaryKeys($loadKeys);
            foreach ($loadedItems as $loadedItem) {
                $list->append($loadedItem);
            }
        }
        return $list;
    }

    /**
     *  Returns all Entities from cache or from database.
     *  @return EntityArray
     */
    public function loadAll() {
        if (!$this->getCache()->isLoadedAll()) {
            return $this->selectAll();
        }
        return $this->getCache()->getEntityArrayAll();
    }
    
    
    /**
     * Creates a EntityArray from a select statemet result
     * @param array $parameters
     * @return \Pvik\Database\ORM\EntityArray 
     */
    public function fillEntityArray($result) {
        $list = new \Pvik\Database\ORM\EntityArray();
        $list->setModelTable($this);
        while ($data = Manager::getInstance()->fetchAssoc($result)) {
            $classname = $this->getEntityClassName();
            $model = new $classname();
            $model->fill($data);
            $list->append($model);
        }
        return $list;
    }
}