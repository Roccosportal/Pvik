<?php
namespace Pvik\Database\ORM\Query\Builder;
/**
 * Represents an update query builder.
 */
class Update {
    /**
     * Returns an empty instance.
     * @param type $modelTableName
     * @return \\Pvik\Database\ORM\Query\Builder\Update
     */
    public static function getEmptyInstance($modelTableName){
        $adapterClassName = \Pvik\Database\Adapter\Adapter::getAdapterClassName('ORM\Query\Builder\Update');
        if($adapterClassName){
            return new $adapterClassName($modelTableName);
        }
        return new Update($modelTableName);
    }
    /**
     * Contains the fields and values that should be updated.
     * @var type 
     */
    protected $fields = array();
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
        return $this;
    }
    /**
     * Sets the where condition statements
     * @param string $where
     */
    public function where($where){
        $this->where['statement'] = $where;
        return $this;
    }
    /**
     * 
     * @param type $modelTableName
     */
    protected function __construct($modelTableName){
        $this->modelTable = \Pvik\Database\ORM\ModelTable::Get($modelTableName);
    }
    /**
     * Sets a field with a value.
     * @param string $field
     * @param type $value
     */
    public function set($field, $value){
        $this->fields[$field] = array(
            'field' => $field,
            'value' => $value
        );
    }
    /**
     * Returns the statement
     * @return \Pvik\Database\SQL\Statement\Statement
     */
    public function getStatement(){
        $statementBuilder = \Pvik\Database\SQL\Statement\Builder\Update::getInstance();
        
        $statement = $statementBuilder->generate(array(
            'fields' => $this->fields,
            'table' => $this->modelTable->GetTableName(),
            'where' =>  $this->where,
        ));
        return $statement;
    }
    /**
     * Execute the update query.
     * @return type
     */
    public function execute(){
        $statement = $this->getStatement();
        return \Pvik\Database\SQL\Manager::GetInstance()->ExecuteStatement($statement);
    }
}


