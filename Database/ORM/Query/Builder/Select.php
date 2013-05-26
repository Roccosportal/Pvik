<?php
namespace Pvik\Database\ORM\Query\Builder;
/**
 * Represents a select query builder.
 */
class Select {
    /**
     * Returns an empty instancce
     * @param string $modelTableName
     * @return \Pvik\Database\ORM\Query\Builder\Select
     */
    public static function getEmptyInstance($modelTableName){
        $adapterClassName = \Pvik\Database\Adapter\Adapter::getAdapterClassName('ORM\Query\Builder\Select');
        if($adapterClassName){
            return new $adapterClassName($modelTableName);
        }
        return new Select($modelTableName);
    }
    /**
     * Contains the fields that should be selected.
     * @var array 
     */
    protected $fields = array();
    /**
     *
     * @var \Pvik\Database\ORM\ModelTable 
     */
    protected $modeTable = null;
    /**
     * Contains the left join options
     * @var array 
     */
    protected $leftJoins = array();
    /**
     * Contains the inner join options
     * @var array 
     */
    protected $innerJoins = array();
    /**
     * Containers the group by fields
     * @var array 
     */
    protected $groupBys = array();
    /**
     * Contains the order by statement
     * @var string 
     */
    protected $orderBy = null;
    /**
     * Contains the limit
     * @var int 
     */
    protected $limit = null;
    /**
     * Contains the offset
     * @var type 
     */
    protected $offset = null;
    /**
     * Contains the statement and parameters for the where condition
     * @var array 
     */
    protected $where = array(
        'statement' => null,
        'parameters' => array(),
    );
    /**
     * Adds an parameter to the where condition
     * @param type $parameter
     */
    public function addParameter($parameter){
        $this->where['parameters'][] = $parameter;
    }
    /**
     * Sets the where condition statements
     * @param string $where
     */
    public function where($where){
        $this->where['statement'] = $where;
    }
    /**
     * 
     * @param string $modelTableName
     */
    protected function __construct($modelTableName){
        if (!is_string($modelTableName)) {
            throw new \Exception('ModelTableName must be a string.');
        }
        $this->modelTable = \Pvik\Database\ORM\ModelTable::get($modelTableName);
        $this->prepare();
    }
    /**
     * Add a field to the select output
     * @param type $field
     */
    public function addField($field){
        $this->fields[$field] = array(
            'field' => $field
        );
    }
    /**
     * Add a inner join
     * @param string $table
     * @param string $joinForeignKey
     * @param string $alias
     */
    public function addInnerJoin($table, $joinForeignKey = null, $alias = null){
        $this->innerJoins[] = array(
            'table' => $table,
            'alias' => $alias,
            'joinForeignKey' => $joinForeignKey,
            'primaryKey' => $this->modelTable->getPrimaryKeyName()
        );
    }
    /**
     * Add a left join
     * @param string $table
     * @param string $joinForeignKey
     * @param string $alias
     */
    public function addLeftJoin($table, $joinForeignKey = null, $alias = null){
        $this->leftJoins[] = array(
            'table' => $table,
            'alias' => $alias,
            'joinForeignKey' => $joinForeignKey,
            'primaryKey' => $this->modelTable->getPrimaryKeyName()
        );
    }
    /**
     * Add a group by field
     * @param string $field
     */
    public function addGroupBy($field){
        $this->groupBys[$field] = array(
            'field' => $field
        );
    }
    /**
     * Set the order by statment
     * @param string $orderBy
     */
    public function orderBy($orderBy){
        $this->orderBy = $orderBy;
    }
    /**
     * Set the limit.
     * @param int $limit
     */
    public function limit($limit){
        $this->limit = $limit;
    }
    /**
     * Set the offset.
     * @param int $offset
     */
    public function offset($offset){
        $this->offset = $offset;
    }
    /**
     * Prepares the select query by the informations from the model table
     */
    protected function prepare(){
        $helper = $this->modelTable->getFieldDefinitionHelper();
        foreach ($helper->getFieldDefinition() as $fieldName => $definition) {
              switch ($helper->getFieldType($fieldName)) {
                case \Pvik\Database\ORM\FieldDefinition\Type::NORMAL:
                case \Pvik\Database\ORM\FieldDefinition\Type::PRIMARY_KEY:
                case \Pvik\Database\ORM\FieldDefinition\Type::FOREIGN_KEY:
                    $this->addField($this->modelTable->getTableName() . '.'. $fieldName);
                    break;
                case \Pvik\Database\ORM\FieldDefinition\Type::MANY_FOREIGN_OBJECTS:
                     // get definition for the foreign table
                    $foreignModelTable = $helper->getModelTable($fieldName);
                    // simple creation for a unique alias
                    $alias = 'JoinAlias_' . $foreignModelTable->getTableName();
                    // generate group_conact
                    $this->addField('GROUP_CONCAT(DISTINCT ' . $alias . '.' . $foreignModelTable->getPrimaryKeyName() . ') as ' . $fieldName);
                    $this->addLeftJoin($foreignModelTable->getTableName(), $helper->getForeignKeyFieldName($fieldName),  $alias);
                    $this->addGroupBy($this->modelTable->getTableName() . '.'. $this->modelTable->getPrimaryKeyName());
                    break;
            }
           
        }
    }
    /**
     * Returns the statement
     * @return \Pvik\Database\SQL\Statement\Statement
     */
    public function getStatement(){        
        $builder = \Pvik\Database\SQL\Statement\Builder\Select::getInstance();   
        $statement = $builder->generate(array(
            'fields' => $this->fields,
            'leftJoins' => $this->leftJoins,
            'innerJoins' => $this->innerJoins,
            'groupBys' => $this->groupBys,
            'orderBy' => $this->orderBy,
            'table' => $this->modelTable->getTableName(),
            'limit' => $this->limit,
            'offset' => $this->offset,
            'where' => $this->where,
        ));
        return $statement;
    }
    /**
     * Executes the select query
     * @return \Pvik\Database\ORM\EntityArray
     */
    public function select(){
        $statement = $this->getStatement();
        $result = \Pvik\Database\SQL\Manager::getInstance()->executeStatement($statement);
        return $this->modelTable->fillEntityArray($result);
    }
    /**
     * Executes the select query and returns a single entity
     * @return \Pvik\Database\ORM\Entity
     */
    public function selectSingle() {
        $list = $this->select();
        if ($list->count() > 0) {
            // return first element
            return $list[0];
        } else {
            return null;
        }
    }
}