<?php
namespace Pvik\Database\ORM\Query\Builder;

class Insert {
    
    public static function getEmptyInstance($modelTableName){
        $adapterClassName = \Pvik\Database\Adapter\Adapter::getAdapterClassName('ORM\Query\Builder\Insert');
        if($adapterClassName){
            return new $adapterClassName($modelTableName);
        }
        return new Insert($modelTableName);
    }
    
    protected $fields = array();
    /**
     *
     * @var \Pvik\Database\ORM\ModelTable 
     */
    protected $modeTable = null;
    
    
    protected function __construct($modelTableName){
        if (!is_string($modelTableName)) {
            throw new \Exception('ModelTableName must be a string.');
        }
        $this->modelTable = \Pvik\Database\ORM\ModelTable::Get($modelTableName);
    }
    
    public function set($field, $value){
        $this->fields[$field] = array(
            'field' => $field,
            'value' => $value
        );
    }
    

    
    public function getStatement(){
        $statementBuilder = \Pvik\Database\SQL\Statement\Builder\Insert::getInstance();
        
        $statement = $statementBuilder->generate(array(
            'fields' => $this->fields,
            'table' => $this->modelTable->GetTableName(),
        ));
        return $statement;
    }
    
    
    public function execute(){
        $statement = $this->getStatement();
        return \Pvik\Database\SQL\Manager::GetInstance()->ExecuteStatement($statement);
    }
}


