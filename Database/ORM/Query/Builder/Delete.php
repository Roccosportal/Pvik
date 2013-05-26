<?php
namespace Pvik\Database\ORM\Query\Builder;
/**
 * Represents a delete query builder
 */
class Delete {
    /**
     * Returns an empty instancce
     * @param string $modelTableName
     * @return \Pvik\Database\ORM\Query\Builder\Delete
     */
    public static function getEmptyInstance($modelTableName){
        $adapterClassName = \Pvik\Database\Adapter\Adapter::getAdapterClassName('ORM\Query\Builder\Delete');
        if($adapterClassName){
            return new $adapterClassName($modelTableName);
        }
        return new Delete($modelTableName);
    }
    /**
     *
     * @var \Pvik\Database\ORM\ModelTable 
     */
    protected $modeTable = null;
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
     * @param string $modelTableName
     */
    protected function __construct($modelTableName){
        $this->modelTable = \Pvik\Database\ORM\ModelTable::get($modelTableName);
    }
    /**
     * Returns the statement
     * @return \Pvik\Database\SQL\Statement\Statement
     */
    public function getStatement(){
        $statementBuilder = \Pvik\Database\SQL\Statement\Builder\Delete::getInstance();
        
        $statement = $statementBuilder->generate(array(
            'table' => $this->modelTable->getTableName(),
            'where' =>  $this->where,
        ));
        return $statement;
    }
    /**
     *  Execute the delete query
     *  @return type
     */
    public function execute(){
        $statement = $this->getStatement();
        return \Pvik\Database\SQL\Manager::getInstance()->executeStatement($statement);
    }
}