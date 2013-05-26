<?php

namespace Pvik\Database\ORM;

/**
 * Represents a row in a table as an object
 */
class Entity {

    /**
     * Contains the name of the model table
     * @var string 
     */
    protected $modelTableName;

    /**
     * Contains the data of the fields
     * @var array 
     */
    protected $fieldData = array();

    /**
     * Fills object with the given data array.
     * And stores the object to the cache
     * @param type $data
     * @return \Pvik\Database\ORM\Entity
     */
    public function fill($data = array()) {
        // fill this class with the data
        foreach ($data as $fieldName => $value) {
            $this->setFieldData($fieldName, $value);
        }
        $modelTable = $this->getModelTable();
        $modelTable->getCache()->store($this);
        return $this;
    }

    /**
     * Returns the name of the model table.
     * @return string
     */
    public function getModelTableName() {
        return $this->modelTableName;
    }

    /**
     * Returns the model table for this entity
     * @return ModelTable
     */
    public function getModelTable() {
        return ModelTable::get($this->modelTableName);
    }

    /**
     * Magic method that allows us to use the field data as properties.
     * Converts foreign keys to objects.
     * @param string $fieldName
     * @return mixed
     * @throws \Exception
     */
    public function __get($fieldName) {
        $modelTable = $this->getModelTable();
        $helper = $modelTable->getFieldDefinitionHelper();
        if ($helper->fieldExists($fieldName)) {
            switch ($helper->getFieldType($fieldName)) {
                case FieldDefinition\Type::PRIMARY_KEY:
                case FieldDefinition\Type::NORMAL:
                case FieldDefinition\Type::FOREIGN_KEY:
                    return $this->getFieldData($fieldName);
                    break;
                case FieldDefinition\Type::FOREIGN_OBJECT:
                    // search for the foreign key reference
                    $foreignKeyFieldName = $helper->getForeignKeyFieldName($fieldName);
                    $foreignKey = $this->getFieldData($foreignKeyFieldName);

                    if ($foreignKey == null) {
                        return null;
                    }
                    $foreignModelTable = $helper->getModelTable($foreignKeyFieldName);
                    return $foreignModelTable->loadByPrimaryKey($foreignKey);
                    break;
                case FieldDefinition\Type::MANY_FOREIGN_OBJECTS:
                    $foreignKeys = $this->getFieldData($fieldName);

                    $modelTable = $helper->getModelTable($fieldName);
                    if ($foreignKeys == null) {
                        $entityArray = new EntityArray();
                        $entityArray->setModelTable($modelTable);
                        return $entityArray;
                    }
                    if (isset($foreignKeys)) {
                        return $modelTable->loadByPrimaryKeys(explode(',', $foreignKeys));
                    } else {
                        throw new \Exception('Foreign keys for ' . $fieldName . ' not found.');
                    }
                    break;
            }
        }
        else if($this->fieldDataExists($fieldName)){
           return $this->getFieldData($fieldName);
        }
        else {
            throw new \Exception('Value ' . $fieldName . ' not found.');
        }
    }

    /**
     * Magic method that allows us to set the field data as properties.
     * @param string $fieldName
     * @param mixed $value
     * @throws \Exception
     */
    public function __set($fieldName, $value) {


        $modelTable = $this->getModelTable();
        //$dataDefinition = $modelTable->getDataDefinition();
        $helper = $modelTable->getFieldDefinitionHelper();
        if ($helper->fieldExists($fieldName)) {
            switch ($helper->getFieldType($fieldName)) {
                case FieldDefinition\Type::PRIMARY_KEY:
                    throw new \Exception('The primary key is only readable: ' . $fieldName);
                    break;
                case FieldDefinition\Type::NORMAL:
                    //$this->data[$key] = $value;
                    $this->setFieldData($fieldName, $value);
                    break;
                case FieldDefinition\Type::FOREIGN_KEY:

                    $primaryKey = $this->getPrimaryKey();
                    if ($primaryKey != null) {
                        $this->getModelTable()->getCache()->deleteForeignKeyReference($this, $fieldName);
                    }
                    $this->setFieldData($fieldName, $value);
                    if ($primaryKey != null) {
                        $this->getModelTable()->getCache()->insertForeignKeyReference($this, $fieldName);
                    }

                    break;
                case FieldDefinition\Type::FOREIGN_OBJECT:
                    throw new \Exception('The object is only readable: ' . $fieldName);
                    break;
                case FieldDefinition\Type::MANY_FOREIGN_OBJECTS:
                    throw new \Exception('The list is only readable: ' . $fieldName);
                    break;
            }
        } else {
            throw new \Exception('Value ' . $fieldName . ' not found.');
        }
    }

    /**
     * Returns the primary key
     * @return mixed
     * @throws \Exception
     */
    public function getPrimaryKey() {
        $primaryKeyName = $this->getPrimaryKeyName();
        if ($primaryKeyName != null) {
            if ($this->fieldDataExists($primaryKeyName)) {
                return $this->getFieldData($primaryKeyName);
            } else {
                return null;
            }
        } else {
            throw new \Exception('The model ' . get_class($this) . ' has no primary key.');
        }
    }
    
    public function getPrimaryKeyName(){
        return $this->getModelTable()->getPrimaryKeyName();
    }

    /**
     * Inserts an entity to the database.
     * @return string primary key
     */
    public function insert() {
        $primaryKey = $this->getPrimaryKey();
        if (empty($primaryKey)) {
            $insertBuilder = Query\Builder\Insert::getEmptyInstance($this->getModelTableName());
            $helper = $this->getModelTable()->getFieldDefinitionHelper();
            foreach ($helper->getFieldList() as $fieldName) {
                switch ($helper->getFieldType($fieldName)) {
                    case FieldDefinition\Type::NORMAL:
                    case FieldDefinition\Type::FOREIGN_KEY:
                        $insertBuilder->set($fieldName, $this->getFieldData($fieldName));
                        break;
                    case FieldDefinition\Type::PRIMARY_KEY:
                        // only insert a value if it is a guid otherwise ignore
                        // the primarykey will be set on the database
                        if ($helper->isGuid($fieldName)) {
                             $insertBuilder->set($fieldName, Core::createGuid());

                        }
                        break;
                }
            }
            $insertBuilder->execute();
            $primaryKey = \Pvik\Database\SQL\Manager::getInstance()->getLastInsertedId();
            // update primarykey in object
            $this->setFieldData($this->getPrimaryKeyName(), $primaryKey);
            $this->getModelTable()->getCache()->insert($this);
            return $primaryKey;
        } else {
            throw new \Exception('The primarykey of this object is already set and the object can\'t be inserted.');
        }
    }

    /**
     * Updates an entity on the database
     * @return mixed
     */
    public function update() {
        $primaryKey = $this->getPrimaryKey();
        if (!empty($primaryKey)) {
            $updateBuilder = Query\Builder\Update::getEmptyInstance($this->getModelTableName());
            $helper = $this->getModelTable()->getFieldDefinitionHelper();
            foreach ($helper->getFieldList() as $fieldName) {
                switch ($helper->getFieldType($fieldName)) {
                    case FieldDefinition\Type::NORMAL:
                    case FieldDefinition\Type::FOREIGN_KEY:
                        $updateBuilder->set($fieldName, $this->getFieldData($fieldName));
                        break;
                }
            }
            $updateBuilder->where($this->getPrimaryKeyName() . '=%s');
            $updateBuilder->addParameter($this->getFieldData($this->getPrimaryKeyName()));
            return $updateBuilder->execute();
        } else {
            throw new \Exception('Primary key isn\'t set, can\'t update');
        }
    }

    /**
     * Deletes an entity on the database
     * @return mixed
     */
    public function delete() {
        $primaryKey = $this->getPrimaryKey();
        if ($primaryKey != null) {
            $builder = Query\Builder\Delete::getEmptyInstance($this->getModelTableName());
            $builder->where($this->getPrimaryKeyName() . '=%s');
            $builder->addParameter($primaryKey);
            $builder->execute();
            $this->getModelTable()->getCache()->delete($this);
        } else {
            throw new \Exception('Can\'t delete model cause it has no primary key.');
        }
    }

    /**
     * Set the a field data value without checking if the value is correct.
     * @param string $fieldName
     * @param mixed $value
     */
    public function setFieldData($fieldName, $value) {
        $this->fieldData[$fieldName] = $value;
    }

    /**
     * Returns the field data value without converting them as in __get()
     * @param string $fieldName
     * @return mixed
     */
    public function getFieldData($fieldName) {
        if (array_key_exists($fieldName, $this->fieldData)) {
            return $this->fieldData[$fieldName];
        } else {
            return null;
        }
    }

    /**
     * Checks if field data value exists
     * @param string $fieldName
     * @return bool
     */
    public function fieldDataExists($fieldName) {
        return (isset($this->fieldData[$fieldName]));
    }

    /**
     * Return an array of the keys instead of the objects for a ManyForeignObjects field
     * @param string $fieldName
     * @return array 
     */
    public function getKeys($fieldName) {
        $modelTable = $this->getModelTable();
        $helper = $modelTable->getFieldDefinitionHelper();
        if (!$helper->isTypeManyForeignObjects($fieldName)) {
            throw new \Exception('The field must have the type ManyForeignObjects.');
        }
        $keysString = $this->getFieldData($fieldName);
        if ($keysString != null) {
            return explode(',', $keysString);
        } else {
            return array();
        }
    }

}
