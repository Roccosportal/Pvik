<?php
namespace Pvik\Database\ORM\Query\Builder;

class Select {
    
    public static function getEmptyInstance($modelTableName){
        $adapterClassName = \Pvik\Database\Adapter\Adapter::getAdapterClassName('ORM\Query\Builder\Select');
        if($adapterClassName){
            return new $adapterClassName($modelTableName);
        }
        return new Select($modelTableName);
    }
    
    protected $fields = array();
    /**
     *
     * @var \Pvik\Database\ORM\ModelTable 
     */
    protected $modeTable = null;
    
    protected $leftJoins = array();
    
    protected $innerJoins = array();
    
    protected $groupBys = array();
    
    protected $orderBy = null;
    
    protected $limit = null;
     
    protected $offset = null;
    
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
        $this->prepare();
    }
    
    public function addField($field){
        $this->fields[$field] = array(
            'field' => $field
        );
    }
    
     public function addInnerJoin($table, $joinForeignKey = null, $alias = null){
        $this->innerJoins[] = array(
            'table' => $table,
            'alias' => $alias,
            'joinForeignKey' => $joinForeignKey,
            'primaryKey' => $this->modelTable->GetPrimaryKeyName()
        );
    }
    
    public function addLeftJoin($table, $joinForeignKey = null, $alias = null){
        $this->leftJoins[] = array(
            'table' => $table,
            'alias' => $alias,
            'joinForeignKey' => $joinForeignKey,
            'primaryKey' => $this->modelTable->GetPrimaryKeyName()
        );
    }
    
    public function addGroupBy($field){
        $this->groupBys[$field] = array(
            'field' => $field
        );
    }
    
    public function orderBy($orderBy){
        $this->orderBy = $orderBy;
    }
    
    public function limit($limit){
        $this->limit = $limit;
    }
    
    public function offset($offset){
        $this->offset = $offset;
    }
    
    
    protected function prepare(){
        $helper = $this->modelTable->GetFieldDefinitionHelper();
        foreach ($helper->GetFieldDefinition() as $fieldName => $definition) {
              switch ($helper->GetFieldType($fieldName)) {
                case 'Normal':
                case 'PrimaryKey':
                case 'ForeignKey':
                    $this->addField($this->modelTable->GetTableName() . '.'. $fieldName);
                    break;
                case 'ManyForeignObjects':
                     // get definition for the foreign table
                    $foreignModelTable = $helper->GetModelTable($fieldName);
                    // simple creation for a unique alias
                    $alias = 'JoinAlias_' . $foreignModelTable->GetTableName();
                    // generate group_conact
                    $this->addField('GROUP_CONCAT(DISTINCT ' . $alias . '.' . $foreignModelTable->GetPrimaryKeyName() . ') as ' . $fieldName);
                    $this->addLeftJoin($foreignModelTable->GetTableName(), $helper->GetForeignKeyFieldName($fieldName),  $alias);
                    $this->addGroupBy($this->modelTable->GetTableName() . '.'. $this->modelTable->GetPrimaryKeyName());
                    break;
            }
           
        }
    }
    
    public function getStatement(){
        
        $builder = \Pvik\Database\SQL\Statement\Builder\Select::getInstance();
        
        $statement = $builder->generate(array(
            'fields' => $this->fields,
            'leftJoins' => $this->leftJoins,
            'innerJoins' => $this->innerJoins,
            'groupBys' => $this->groupBys,
            'orderBy' => $this->orderBy,
            'table' => $this->modelTable->GetTableName(),
            'limit' => $this->limit,
            'offset' => $this->offset,
            'where' => $this->where,
        ));
        return $statement;
    }
    
    
    public function select(){
        $statement = $this->getStatement();
        $result = \Pvik\Database\SQL\Manager::GetInstance()->ExecuteStatement($statement);
        return $this->modelTable->FillEntityArray($result);
    }
    
   
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


