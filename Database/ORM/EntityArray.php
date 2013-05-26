<?php

namespace Pvik\Database\ORM;

/**
 * A class that contains a list of Entites.
 */
class EntityArray extends \ArrayObject {

    /**
     * Contains the list of fields that get sorted when running a sort function.
     * @var array 
     */
    protected $sortFieldLists = null;

    /**
     * Contains the ModelTable of the models.
     * @var ModelTable 
     */
    protected $modelTable;

    /**
     * Sets the ModelTable of the models
     * @param ModelTable $modelTable 
     */
    public function setModelTable(ModelTable $modelTable = null) {
        $this->modelTable = $modelTable;
    }

    /**
     * Get the ModelTable of the models
     * @return ModelTable 
     */
    public function getModelTable() {
        return $this->modelTable;
    }

    /**
     * Run a distinct on a field
     * @param string $fieldName
     * @return EntityArray 
     */
    public function distinct($fieldName) {
        $keyList = array();
        $list = new EntityArray();
        foreach ($this as $object) {
            if (!in_array($object->$fieldName, $keyList)) {
                array_push($keyList, $object->$fieldName);
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where the field is equals one of the given values.
     * @param string $fieldName
     * @param IteratorAggregate $values
     * @return EntityArray 
     */
    public function filterIn($fieldName, IteratorAggregate $values) {
        $list = new ModelArray();
        $list->setModelTable($this->getModelTable());
        if ($values === null)
            return $list;


        if (!is_array($values) && !($values instanceof IteratorAggregate))
            throw new \Exception('The parameters keys must be an array.');


        foreach ($this as $object) {
            foreach ($values as $value) {
                if ($object->$fieldName === $value) {
                    $list->append($object);
                    break;
                }
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where the field is not equals one of the given values.
     * @param string $fieldName
     * @param IteratorAggregate $values
     * @return EntityArray 
     */
    public function filterNotIn($fieldName, $values) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        if ($values === null)
            return $list;


        if (!is_array($values) && !($values instanceof IteratorAggregate))
            throw new \Exception('The parameters keys must be an array.');


        foreach ($this as $object) {
            $hasValue = false;
            foreach ($values as $value) {
                if ($object->$fieldName == $value) {
                    $hasValue = true;
                    break;
                }
            }
            if ($hasValue == false) {
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where a field value is equals to the value.
     * @param string $fieldName
     * @param mixed $value
     * @return EntityArray 
     */
    public function filterEquals($fieldName, $value) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        foreach ($this as $object) {
            if ($object->$fieldName === $value) {
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where a field value is higher than the value.
     * @param string $fieldName
     * @param mixed $value
     * @return EntityArray 
     */
    public function filterHeigher($fieldName, $value) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        foreach ($this as $object) {
            if ($object->$fieldName > $value) {
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where the field value is higher or equals to the value.
     * @param string $fieldName
     * @param type $value
     * @return EntityArray 
     */
    public function filterHeigherEquals($fieldName, $value) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        foreach ($this as $object) {
            if ($object->$fieldName >= $value) {
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where the field is lower than the value
     * @param string $fieldName
     * @param mixed $value
     * @return EntityArray 
     */
    public function filterLower($fieldName, $value) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        foreach ($this as $object) {
            if ($object->$fieldName < $value) {
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where the field value is lower or equals to the value.
     * @param string $fieldName
     * @param mixed $value
     * @return EntityArray 
     */
    public function filterLowerEquals($fieldName, $value) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        foreach ($this as $object) {
            if ($object->$fieldName <= $value) {
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where the field value is not equals the value.
     * @param string $fieldName
     * @param mixed $value
     * @return EntityArray 
     */
    public function filterNotEquals($fieldName, $value) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        foreach ($this as $object) {
            if ($object->$fieldName !== $value) {
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where the field value starts with the value.
     * @param string $fieldName
     * @param string $value
     * @return EntityArray 
     */
    public function filterStartsWith($fieldName, $value) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        foreach ($this as $object) {
            if ($object->$fieldName != null && strpos($object->$fieldName, $value) === 0) {
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where the field value ends with the value.
     * @param string $fieldName
     * @param string $value
     * @return EntityArray 
     */
    public function filterEndsWith($fieldName, $value) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        foreach ($this as $object) {
            if ($object->$fieldName != null) {
                $lengthField = strlen($object->$fieldName);
                $lengthValue = strlen($value);
                if ($lengthField >= $lengthValue) {
                    if (strpos($object->$fieldName, $value) === ($lengthField - $lengthValue)) {
                        $list->append($object);
                    }
                }
            }
        }
        return $list;
    }

    /**
     * Returns a list of models where the field value contains the value.
     * @param string $fieldName
     * @param string $value
     * @return EntityArray 
     */
    public function filterContains($fieldName, $value) {
        $list = new EntityArray();
        $list->setModelTable($this->getModelTable());
        foreach ($this as $object) {
            if (strpos($object->$fieldName, $value) !== false) {
                $list->append($object);
            }
        }
        return $list;
    }

    /**
     * Returns a list of an field.
     * @param type $fieldName
     * @return EntityArray 
     */
    public function getList($fieldName) {
        $list = new EntityArray();
        if ($this->modelTable != null) {
            $helper = $this->modelTable->getFieldDefinitionHelper();
            if ($helper->isTypeForeignObject($fieldName) || $helper->isTypeManyForeignObjects($fieldName)) {
                $list->setModelTable($helper->getModelTable($fieldName));
            }
        }
        foreach ($this as $object) {
            $list->append($object->$fieldName);
        }
        return $list;
    }

    /**
     * Returns the first object in the list.
     * @return Entity; 
     */
    public function getFirst() {
        foreach ($this as $object) {
            return $object;
        }
        return null;
    }

    /**
     * Returns the first objects in the list
     * @param int $maxCount
     * @return EntityArray 
     */
    public function getFirsts($maxCount) {
        $list = new EntityArray();
        if ($this->modelTable != null) {
            $list->setModelTable($this->modelTable);
        }
        $count = 1;
        foreach ($this as $object) {
            $list->append($object);
            if ($count == $maxCount) {
                break;
            } else {
                $count++;
            }
        }
        return $list;
    }

    /**
     * Returns the objects between start and end number.
     * @param int $start
     * @param int $length
     * @return EntityArray 
     */
    public function getBetween($start, $length) {
        $list = new EntityArray();
        if ($this->modelTable != null) {
            $list->setModelTable($this->modelTable);
        }
        $count = 1;
        foreach ($this as $object) {
            if ($count >= $start) {
                if ($start + $length != $count) {
                    $list->append($object);
                } else {
                    break;
                }
            }
            $count++;
        }
        return $list;
    }

    /**
     * Sorts the list to specified arguments.
     * Allows more then one argument.
     * @example Sort('Date'); // sorts the list asscending to field date
     * @example Sort('-Date'); // sorts the list descending to field date
     * @example Sort('+Date'); // equals to Sort('Date')
     * @example Sort('Author->name'); // author is a ForeignObject and the list gets sorted assencding to the name
     * @example Sort('Date', '-Author->name'); // sorts first to field date and than to sub field of author
     * @example Sort('Date', 'Author->country->name', 'Author->name');
     * @param type $sortArgument
     * @return EntityArray 
     */
    public function sort($sortArgument) {
        $arguments = func_get_args();
        $fieldLists = array();
        foreach ($arguments as $argument) {
            array_push($fieldLists, $this->convertStringToFieldList($argument, true));
        }
        $this->sortFieldLists = $fieldLists;
        $this->uasort(array($this, 'Compare'));
        return $this;
    }

    /**
     * Converts a field string from 'Author->country->name' to an array
     * @param string $string
     * @param bool $isSortable
     * @return array 
     */
    protected function convertStringToFieldList($string, $isSortable = false) {
        if ($isSortable) {
            $sort = 'ASC';
            if ($string[0] == '+') {
                $string = substr($string, 1);
            } elseif ($string[0] == '-') {
                $string = substr($string, 1);
                $sort = 'DESC';
            }
        }

        $fields = explode('->', $string);

        $fieldList = array();
        if ($isSortable)
            $fieldList['Sort'] = $sort;
        $fieldList['Fields'] = $fields;
        return $fieldList;
    }

    /**
     * Compares two obects.
     * @param mixed $object1
     * @param mixed $object2
     * @return int 
     */
    public function compare($object1, $object2) {
        $fieldLists = $this->sortFieldLists;
        foreach ($fieldLists as $fieldList) {
            $result = $this->compareFieldList($object1, $object2, $fieldList);
            if ($result != 0) {
                return $result;
            }
        }
        return 0;
    }

    /**
     * Compares a two objects to a field list.
     * @param mixed $object1
     * @param mixed $object2
     * @param array $fieldList
     * @return int 
     */
    protected function compareFieldList($object1, $object2, $fieldList) {
        $sort = $fieldList['Sort'];
        $fields = $fieldList['Fields'];
        $countFields = count($fields);
        for ($index = 0; $index < $countFields; $index++) {
            $field = $fields[$index];
            if ($object1->$field == null && $object2->$field == null) {
                return 0;
            } elseif ($object1->$field == null) {
                if ($sort == 'ASC') {
                    return -1;
                }
                return 1;
            } elseif ($object2->$field == null) {
                if ($sort == 'ASC') {
                    return 1;
                }
                return -1;
            }
            // if it is the last item in the field list
            elseif ($index == ($countFields - 1)) {
                if ($object1->$field == $object2->$field) {
                    return 0;
                } elseif ($object1->$field < $object2->$field) {
                    if ($sort == 'ASC') {
                        return -1;
                    }
                    return 1;
                } elseif ($sort == 'ASC') {
                    return 1;
                }
                return -1;
            } else {
                $object1 = $object1->$field;
                $object2 = $object2->$field;
            }
        }
    }

    /**
     * Merge a EntityArray to current EntityArray
     * @param EntityArray $entityArray
     * @return EntityArray 
     */
    public function merge(EntityArray $entityArray) {
        $list = new EntityArray();
        // set model table if both arrays have the same model table
        if ($this->getModelTable() == $entityArray->getModelTable()) {
            $list->setModelTable($this->getModelTable());
        }
        foreach ($this as $object) {

            $list->append($object);
        }
        foreach ($entityArray as $object) {

            $list->append($object);
        }
        return $list;
    }

    /**
     * Checks if one of the models in list have the value.
     * @param string $fieldName
     * @param mixed $value
     * @return bool 
     */
    public function hasValue($fieldName, $value) {
        foreach ($this as $object) {
            if ($object->$fieldName === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if none of the models in list have the value.
     * @param type $fieldName
     * @param type $value
     * @return type 
     */
    public function hasNotValue($fieldName, $value) {
        return !$this->HasValue($fieldName, $value);
    }

    /**
     * Loads a list of fields in the model.
     * @example LoadList('Author->Books'); // returns a list of books
     * @param string $fields
     * @return EntityArray 
     */
    public function loadList($fields) {
        if ($this->modelTable == null) {
            throw new \Exception('ModelTable must be set to use this function.');
        }
        $fieldList = $this->convertStringToFieldList($fields);
        $modelTable = $this->modelTable;
        $list = $this;
        foreach ($fieldList['Fields'] as $fieldName) {
            // load definition for current field
            $helper = $modelTable->getFieldDefinitionHelper();

            if (!$helper->fieldExists($fieldName)) {
                throw new \Exception('Field ' . $fieldName . ' must be in field definition');
            }
            switch ($helper->getFieldType($fieldName)) {
                case 'ForeignObject':
                    $foreignKeyFieldName = $helper->getForeignKeyFieldName($fieldName);
                    $foreignModelTable = $helper->getModelTable($foreignKeyFieldName);
                    $keys = array();
                    // get all keys
                    foreach ($list as $object) {
                        if ($object != null && $object->$foreignKeyFieldName !== null) {
                            array_push($keys, $object->$foreignKeyFieldName);
                        }
                    }
                    // load all foreign objects
                    $list = $foreignModelTable->loadByPrimaryKeys($keys);
                    // set for the next field in the loop
                    $modelTable = $foreignModelTable;
                    break;
                case 'ManyForeignObjects':
                    $foreignModelTable = $helper->getModelTable($fieldName);
                    $keys = array();
                    foreach ($list as $object) {
                        if ($object != null) {
                            $keys = array_merge($keys, $object->getKeys($fieldName));
                        }
                    }
                    $list = $foreignModelTable->loadByPrimaryKeys($keys);
                    // set for the next field in the loop
                    $modelTable = $foreignModelTable;
                    break;
                default:
                    return null;
                    break;
            }
        }
        return $list;
    }

    /**
     * Checks if the list is empty.
     * @return bool 
     */
    public function isEmpty() {
        if ($this->count() == 0) {
            return true;
        } else {
            return false;
        }
    }

}
