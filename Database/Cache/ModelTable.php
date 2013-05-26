<?php

namespace Pvik\Database\Cache;

/**
 * Manages the cache for a model table
 */
class ModelTable {

    /**
     * Contains the already loaded entities.
     * @var array 
     */
    protected $cache;

    /**
     * Contains a value that indicates if all Entities are loaded.
     * @var bool 
     */
    protected $isLoadedAll;

    /**
     * Contains the EntityArray of all Entities if all are loaded.
     * @var \Pvik\Database\ORM\EntityArray
     */
    protected $entityArrayAll;

    /**
     * Contains the ModelTable for this Cache
     * @var \Pvik\Database\ORM\ModelTable 
     */
    protected $modelTable;

    /**
     * 
     * @param \Pvik\Database\ORM\ModelTable $modelTable
     */
    public function __construct(\Pvik\Database\ORM\ModelTable $modelTable) {
        $this->modelTable = $modelTable;
        $this->cache = array();
        $this->isLoadedAll = false;
        $this->entityArrayAll = null;
    }

    /**
     * Returns all instances that are in the cache
     * @return \Pvik\Database\ORM\EntityArray
     */
    public function getAllCacheInstances() {
        $instances = new \Pvik\Database\ORM\EntityArray();
        $instances->setModelTable($this->modelTable);
        foreach ($this->cache as $value) {
            $instances->append($value['Instance']);
        }
        return $instances;
    }

    /**
     * Stores a instance of model into the cache.
     * @param \Pvik\Database\ORM\Entity $instance 
     */
    public function store(\Pvik\Database\ORM\Entity $instance) {
        if ($instance != null) {
            $primaryKey = $instance->getPrimaryKey();
            if ($primaryKey != '') {
                // store in cache
                $this->cache[$primaryKey] = array(
                    'Instance' => $instance,
                    'Timestamp' => microtime()
                );
            }
        }
    }

    /**
     * Loads a Model from cache by its primary key.
     * @param string $primaryKey
     * @return \Pvik\Database\ORM\Entity 
     */
    public function loadByPrimaryKey($primaryKey) {
        if (!is_string($primaryKey) && !is_int($primaryKey)) {
            throw new \Exception('primary key must be a string or int');
        }
        if (isset($this->cache[$primaryKey])) {
            return $this->cache[$primaryKey]['Instance'];
        }
        return null;
    }

    /**
     * Indicates if all entities are loaded in cache from the ModelTable
     * @return bool
     */
    public function isLoadedAll() {
        return $this->isLoadedAll;
    }

    /**
     * Returns the list all entities if all are loaded into cache
     * @return \Pvik\Database\ORM\EntityArray
     */
    public function getEntityArrayAll() {
        return $this->entityArrayAll;
    }

    /**
     * Set the list of all entities that are loaded into the cache
     * @param \Pvik\Database\ORM\EntityArray $entityArray
     */
    public function setEntityArrayAll(\Pvik\Database\ORM\EntityArray $entityArray) {
        $this->isLoadedAll = true;
        $this->entityArrayAll = $entityArray;
    }

    /**
     * Updates the cache.
     * @param \Pvik\Database\ORM\Entity $object
     */
    public function insert(\Pvik\Database\ORM\Entity $object) {
        // update Foreign Objects that are in cache
        $helper = $this->modelTable->getFieldDefinitionHelper();
        foreach ($helper->getFieldList() as $fieldName) {
            if ($helper->isTypeForeignKey($fieldName)) {
                $this->insertForeignKeyReference($object, $fieldName);
            }
        }
        $this->store($object);
    }

    /**
     * Updates the reference from entities to the object by the current foreign key for a field name
     * @param \Pvik\Database\ORM\Entity $object
     * @param string $fieldName
     */
    public function insertForeignKeyReference(\Pvik\Database\ORM\Entity $object, $fieldName) {
        //  get the key that refers to the foreign object (AuthorID  from a book)
        $helper = $this->modelTable->getFieldDefinitionHelper();
        $foreignKey = $object->getFieldData($fieldName);
        if (isset($foreignKey) && $foreignKey != 0) {
            $foreignModelTable = $helper->getModelTable($fieldName);
            $foreignObject = $foreignModelTable->getCache()->loadByPrimaryKey($foreignKey);  // look if object is in cache
            if ($foreignObject != null) {
                $foreignHelper = $foreignModelTable->getFieldDefinitionHelper();
                // find data field from type ManyForeignObjects that have a reference to this model table 
                foreach ($foreignHelper->getManyForeignObjectsFieldList() as $foreignFieldName) {
                    if ($foreignHelper->getModelTableName($foreignFieldName) == $this->modelTable->getModelTableName() // Author.Books is refering to BooksModelTable
                            && $foreignHelper->getForeignKeyFieldName($foreignFieldName) == $fieldName) {  // Author.Books.ForeignKey is AuthorID
                        // add primary key from inserted object to list
                        $old = $foreignObject->getFieldData($foreignFieldName);
                        $new = $old . ',' . $object->getPrimaryKey();
                        $foreignObject->setFieldData($foreignFieldName, $new);

                        break;
                    }
                }
            }
        }
    }

    /**
     * Updates the cache.
     * @param \Pvik\Database\ORM\Entity $object 
     */
    public function delete(\Pvik\Database\ORM\Entity $object) {
        // update foreign objects
        $helper = $this->modelTable->getFieldDefinitionHelper();

        foreach ($helper->getFieldList() as $fieldName) {
            if ($helper->isTypeForeignKey($fieldName)) {
                $this->deleteForeignKeyReference($object, $fieldName);
            }
        }
    }

    /**
     * Deletes the reference from entities to the object by the current foreign key for a field name
     * @param \Pvik\Database\ORM\Entity $object
     * @param string $fieldName
     */
    public function deleteForeignKeyReference(\Pvik\Database\ORM\Entity $object, $fieldName) {
        $helper = $this->modelTable->getFieldDefinitionHelper();
        $foreignModelTable = $helper->getModelTable($fieldName);
        //  get the key that refers to the foreign object (AuthorID  from a book)
        $foreignKey = $object->getFieldData($fieldName);
        $foreignObject = $foreignModelTable->getCache()->loadByPrimaryKey($foreignKey);
        // if object exist in cache and needs to be updated
        if ($foreignObject != null) {
            // look through foreign model
            $foreignHelper = $foreignModelTable->getFieldDefinitionHelper();
            foreach ($foreignHelper->getManyForeignObjectsFieldList() as $foreignModelTableFieldName) {
                // searching for a ManyForeignObjects field with ForeignKey reference to this field
                if ($foreignHelper->getModelTableName($foreignModelTableFieldName) == $this->modelTable->getModelTableName()  // Author.Books is refering to BooksModelTable
                        && $foreignHelper->getForeignKeyFieldName($foreignModelTableFieldName) == $fieldName) {  // Author.Books.ForeignKey is AuthorID
                    $oldKeys = $foreignObject->getFieldData($foreignModelTableFieldName);
                    // delete from old keys
                    $foreignObject->setFieldData($foreignModelTableFieldName, str_replace($object->getPrimaryKey(), '', $oldKeys));
                    break;
                }
            }
        }
    }

}