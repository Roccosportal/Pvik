<?php

namespace Pvik\Database\ORM\FieldDefinition;
use \Pvik\Database\ORM\ModelTable;
/**
 * A helper class for the field definition of model tables
 */
class Helper {

    /**
     * Field definition of an model table.
     * @var array 
     */
    protected $fieldDefinition;

    /**
     * A list of fields in the current field definition.
     * @link GetFieldList()
     * @var \ArrayObject 
     */
    protected $fieldList;

    /**
     * A list of fields in the current field definition of type ManyForeignObjects.
     * @link GetManyForeignObjectsFieldList()
     * @var \ArrayObject 
     */
    protected $manyForeignObjectsFieldList;

    /**
     * A list of fields in the current field definition of type ForeignKey.
     * @link GetForeignKeysFieldList()
     * @var \ArrayObject 
     */
    protected $foreignKeysFieldList;

    /**
     * A list of fields in the current field definition of type ForeignObject.
     * @link GetForeignObjectsFieldList()
     * @var \ArrayObject 
     */
    protected $foreignObjectsFieldList;

    /**
     * Contains the origin ModelTable of the field definition.
     * @var ModelTable 
     */
    protected $originModelTable;

    /**
     *
     * @param array $fieldDefinition
     * @param ModelTable $originModelTable 
     */
    public function __construct($fieldDefinition, \Pvik\Database\ORM\ModelTable $originModelTable) {
        $this->fieldDefinition = $fieldDefinition;
        $this->originModelTable = $originModelTable;
    }

    /**
     * Returns the origin ModelTable of the field definition
     * @return ModelTable 
     */
    public function getOriginModelTable() {
        return $this->originModelTable;
    }

    /**
     * Returns the field definition of the ModelTable.
     * @return type 
     */
    public function getFieldDefinition() {
        return $this->fieldDefinition;
    }

    /**
     * Returns a list of fields in the current field definition.
     * Saves the result and just loop once trough the field definition to get all fields.
     * @return ArrayObject 
     */
    public function getFieldList() {
        // just run it once and save results
        if ($this->fieldList == null) {
            $this->fieldList = new \ArrayObject();
            foreach ($this->getFieldDefinition() as $fieldName => $definition) {
                $this->fieldList->append($fieldName);
            }
        }
        return $this->fieldList;
    }

    /**
     * Returns a list of fields in the current field definition of type ManyForeignObjects.
     * Saves the result and just loop once trough the field definition to get all fields.
     * @return \ArrayObject
     */
    public function getManyForeignObjectsFieldList() {
        // just run it once and save results
        if ($this->manyForeignObjectsFieldList == null) {
            $this->manyForeignObjectsFieldList = new \ArrayObject();
            foreach ($this->getFieldList() as $fieldName) {
                if ($this->isTypeManyForeignObjects($fieldName)) {
                    $this->manyForeignObjectsFieldList->append($fieldName);
                }
            }
        }
        return $this->manyForeignObjectsFieldList;
    }

    /**
     * Returns a list of fields in the current field definition of type ForeignKey.
     * Saves the result and just loop once trough the field definition to get all fields.
     * @return \ArrayObject 
     */
    public function getForeignKeysFieldList() {
        // just run it once and save results
        if ($this->foreignKeysFieldList == null) {
            $this->foreignKeysFieldList = new \ArrayObject();
            foreach ($this->getFieldList() as $fieldName) {
                if ($this->isTypeForeignKey($fieldName)) {
                    $this->foreignKeysFieldList->append($fieldName);
                }
            }
        }
        return $this->foreignKeysFieldList;
    }

    /**
     * Returns a list of fields in the current field definition of type ForeignObject.
     * Saves the result and just loop once trough the field definition to get all fields.
     * @return \ArrayObject 
     */
    public function getForeignObjectsFieldList() {
        // just run it once and save results
        if ($this->foreignObjectsFieldList == null) {
            $this->foreignObjectsFieldList = new \ArrayObject();
            foreach ($this->getFieldList() as $fieldName) {
                if ($this->isTypeForeignObject($fieldName)) {
                    $this->foreignObjectsFieldList->append($fieldName);
                }
            }
        }
        return $this->foreignObjectsFieldList;
    }

    /**
     * Checks if a field exists in the field definition.
     * @param string $fieldName
     * @return bool 
     */
    public function fieldExists($fieldName) {
        if (isset($this->fieldDefinition[$fieldName])) {
            return true;
        }
        return false;
    }

    /**
     * Returns the definition array of an field or null if the field doesn't exists.
     * @param string $fieldName
     * @return array 
     */
    public function getField($fieldName) {
        if ($this->fieldExists($fieldName)) {
            return $this->fieldDefinition[$fieldName];
        }
        return null;
    }

    /**
     * Checks if a value field for a definition field is set up.
     * @param string $fieldName
     * @param string $valueName
     * @return bool 
     */
    public function hasFieldValue($fieldName, $valueName) {
        $field = $this->getField($fieldName);
        if ($field != null && isset($field[$valueName])) {
            return true;
        }
        return false;
    }

    /**
     * Returns the value of a value field in a field definition or null.
     * @param string $fieldName
     * @param string $valueName
     * @return string 
     */
    public function getFieldValue($fieldName, $valueName) {
        if ($this->hasFieldValue($fieldName, $valueName)) {
            $field = $this->getField($fieldName);
            return $field[$valueName];
        }
        return null;
    }

    /**
     * Returns the type of a field or null.
     * @param string $fieldName
     * @return string 
     */
    public function getFieldType($fieldName) {
        return $this->getFieldValue($fieldName, 'Type');
    }

    /**
     * Checks if the type is ForeignKey of a field.
     * @param string $fieldName
     * @return bool 
     */
    public function isTypeForeignKey($fieldName) {
        $fieldType = $this->getFieldType($fieldName);
        if ($fieldType == Type::FOREIGN_KEY) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the type is PrimaryKey of a field.
     * @param string $fieldName
     * @return bool 
     */
    public function isTypePrimaryKey($fieldName) {
        $fieldType = $this->getFieldType($fieldName);
        if ($fieldType == Type::PRIMARY_KEY) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the type is ForeignObject of a field.
     * @param string $fieldName
     * @return bool 
     */
    public function isTypeForeignObject($fieldName) {
        $fieldType = $this->getFieldType($fieldName);
        if ($fieldType == Type::FOREIGN_OBJECT) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the type is ManyForeignObjects of a field.
     * @param string $fieldName
     * @return bool 
     */
    public function isTypeManyForeignObjects($fieldName) {
        $fieldType = $this->getFieldType($fieldName);
        if ($fieldType == Type::MANY_FOREIGN_OBJECTS) {
            return true;
        }
        return false;
    }

    /**
     * Returns the ForeignKey field name for an field of type ForeignObject or ManyForeignObjects.
     * @param string $fieldName
     * @return string 
     */
    public function getForeignKeyFieldName($fieldName) {
        if ($this->isTypeForeignObject($fieldName) || $this->isTypeManyForeignObjects($fieldName)) {
            return $this->getFieldValue($fieldName, 'ForeignKey');
        } else {
            throw new \Exception('Only the types ForeignObject and ManyForeignObjects have a ModelTable.');
        }
    }

    /**
     * Returns the name of the ModelTable value field for the definition field or null.
     * @param string $fieldName
     * @return string 
     */
    public function getModelTableName($fieldName) {
        $field = $this->getField($fieldName);
        if ($field != null) {
            return $this->getFieldValue($fieldName, 'ModelTable');
        }
        return null;
    }

    /**
     * Returns the ModelTable for a ForeignKey, ManyForeignObjects or ForeignObject field.
     * @param string $fieldName
     * @return ModelTable 
     */
    public function getModelTable($fieldName) {
        if ($this->isTypeForeignKey($fieldName) || $this->isTypeManyForeignObjects($fieldName)) {
            $modelTableName = $this->getModelTableName($fieldName);

            return ModelTable::get($modelTableName);
        } elseif ($this->isTypeForeignObject($fieldName)) {
            $foreignKeyFieldName = $this->getForeignKeyFieldName($fieldName);
            $modelTableName = $this->getModelTableName($foreignKeyFieldName);


            return ModelTable::get($modelTableName);
        } else {
            throw new \Exception('Only the types ForeignKey, ManyForeignObjects, ForeignObject have a ModelTable.');
        }
    }

    /**
     * Returns the ModelTable name of a field of type ForeignObject or null.
     * @param string $fieldName
     * @return string 
     */
    public function getModelTableNameForForeignObject($fieldName) {
        if ($this->isTypeForeignObject($fieldName)) {
            $foreignKeyFieldName = $this->getFieldValue($fieldName, 'ForeignKey');
            if ($foreignKeyFieldName != null && $foreignKeyFieldName != '') {
                return $this->getModelTableName($foreignKeyFieldName);
            }
        } else {
            throw new \Exception($fieldName . ' is not an ForeignObject.');
        }
        return null;
    }

    /**
     * Checks is a PrimaryKey is set up as Guid.
     * @param string $fieldName
     * @return bool 
     */
    public function isGuid($fieldName) {
        if ($this->isTypePrimaryKey($fieldName)) {
            $value = $this->getFieldValue($fieldName, 'IsGuid');
            if ($value === true) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new \Exception('Only a primary key can have the field IsGUID.');
        }
    }

}