<?php
namespace Pvik\Database\ORM\Query\Builder;

class Update {
    
    public static function getEmptyInstance($modelTableName){
        $adapterClassName = \Pvik\Database\Adapter\Adapter::getAdapterClassName('ORM\Query\Builder\Update');
        if($adapterClassName){
            return new $adapterClassName($modelTableName);
        }
        return new Update($modelTableName);
    }
    
    protected $fields = array();
    /**
     *
     * @var \Pvik\Database\ORM\ModelTable 
     */
    protected $modeTable = null;
    
    
    protected $where = array(
        'statement' => null,
        'parameters' => array(),
    );
     
  
    
    
    public function addParameter($parameter){
        $this->where['parameters'][] = $parameter;
    }
    
    public function where($where){
        $this->where['statement'] = $where;
    }

    
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
        $statementBuilder = \Pvik\Database\SQL\Statement\Builder\Update::getInstance();
        
        $statement = $statementBuilder->generate(array(
            'fields' => $this->fields,
            'table' => $this->modelTable->GetTableName(),
            'where' =>  $this->where,
        ));
        return $statement;
    }
    
    
    public function execute(){
        $statement = $this->getStatement();
        return \Pvik\Database\SQL\Manager::GetInstance()->ExecuteStatement($statement);
    }
}


