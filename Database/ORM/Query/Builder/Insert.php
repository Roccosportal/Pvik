<?php
namespace Pvik\Database\ORM\Query\Builder;
/**
 * Represents an insert query
 */
class Insert {
    /**
     * Returns an empty instancce
     * @param string $modelTableName
     * @return \Pvik\Database\ORM\Query\Builder\Insert
     */
    public static function getEmptyInstance($modelTableName){
        $adapterClassName = \Pvik\Database\Adapter\Adapter::getAdapterClassName('ORM\Query\Builder\Insert');
        if($adapterClassName){
            return new $adapterClassName($modelTableName);
        }
        return new Insert($modelTableName);
    }
    /**
     * Contains the fields and values that should be inserted.
     * @var type 
     */
    protected $fields = array();
    /**
     *
     * @var \Pvik\Database\ORM\ModelTable 
     */
    protected $modeTable = null;
    /**
     * 
     * @param string $modelTableName
     */
    protected function __construct($modelTableName){
        $this->modelTable = \Pvik\Database\ORM\ModelTable::get($modelTableName);
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
        $statementBuilder = \Pvik\Database\SQL\Statement\Builder\Insert::getInstance();
        
        $statement = $statementBuilder->generate(array(
            'fields' => $this->fields,
            'table' => $this->modelTable->getTableName(),
        ));
        return $statement;
    }
    /**
     * Execute the insert query.
     * @return type
     */
    public function execute(){
        $statement = $this->getStatement();
        return \Pvik\Database\SQL\Manager::getInstance()->executeStatement($statement);
    }
}