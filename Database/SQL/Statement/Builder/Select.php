<?php
namespace Pvik\Database\SQL\Statement\Builder;

class Select extends Generic{
    protected static $Instance;
    
    public static function getInstance(){
        if(!self::$Instance){
            $adapterClassName  = \Pvik\Database\Adapter\Adapter::getAdapterClassName('SQL\Statement\Builder\Select');
            if($adapterClassName){
                self::$Instance = new $adapterClassName();
            }
            else{
                self::$Instance = new Select();
            }
        }
        return self::$Instance;
    }
    
    protected function __construct(){
        
    }
    
    protected function generateSelect($options){
        $fields = isset($options['fields']) ? $options['fields'] : null;
        
        if(!$fields || count($fields) == 0 ){
            $fields = array('*');
        }
        $statement = 'SELECT ';
        
        $first = true;
        foreach($fields as $field){
             if (!$first) {
                // add , at the end
                $statement .= ', ';
             }
             else {
                 $first = false;
             }
             $statement .= $field['field'];
        }
        return $statement;
    }
    
    protected function generateFrom($options){
        $table = isset($options['table']) ? $options['table'] : null;
        
        if(!$table){
            throw new \Exception();
        }
        
        $statement = 'FROM ';
        $statement .=  $table;
        return $statement;
    }
    
    protected function generateLeftJoins($options){
        $joins = isset($options['leftJoins']) ? $options['leftJoins'] : null;
        return $this->generateJoins('LEFT JOIN', $joins, $options['table']);
    }
    
    protected function generateInnerJoins($options){
        $joins = isset($options['innerJoins']) ? $options['innerJoins'] : null;
        return $this->generateJoins('INNER JOIN', $joins, $options['table']);
    }
    
    protected function generateJoins($joinType, $joins, $table){
        if(!$joins){
          return '';
        }
        $statement = '';
        foreach($joins as $join){
            $statement .= $this->generateJoin($joinType, $join['table'], $join['alias'],  $join['joinForeignKey'], $join['primaryKey'], $table);
        }
        return $statement;
    }
    
    protected function generateJoin($joinType, $joinTable, $joinAlias, $joinForeignKey, $primaryKey, $table){
        $statement = ' ' . $joinType . ' ' . $joinTable;
        if($joinAlias != null){
             $statement .= ' as ' . $joinAlias;
        }
        else {
            $joinAlias = $joinTable;
        }
        if(!$joinForeignKey){
            $joinForeignKey = $primaryKey;
        }
        $statement .= ' ON ' . $joinAlias . '.' . $joinForeignKey;
        $statement .= ' = ' . $table . '.' . $primaryKey;
       
        return $statement;
    }

    protected function generateGroupBy($options){
        $groupBys = isset($options['groupBys']) ? $options['groupBys'] : null;
        
        if(!$groupBys){
           return '';
        }
        $first = true;
        
        $statement = 'GROUP BY ';
        foreach($groupBys as $groupBy){
            if (!$first) {
                // add , at the end
                $statement .= ', ';
            }
            $statement .= $groupBy['field'];
        }
        return $statement;
    }
    
    protected function generateOrderBy($options){
        $orderBy = isset($options['orderBy']) ? $options['orderBy'] : null;
        if(!$orderBy || empty($orderBy)){
           return '';
        }
  
        $statement = 'ORDER BY ';
        
        $statement .=  $orderBy;
        return $statement;
    }
    
    protected function generateLimit($options){
        $limit = isset($options['limit']) ? $options['limit'] : null;
        $offset = isset($options['offset']) ? $options['offset'] : null;
        
        if(!$limit && !$offset){
           return '';
        }
        
        if(!$offset){
          return 'LIMIT ' . $limit;
        }
        
        if(!$limit){
            $limit = 99999999999; // this should be enough
        }
  
        return 'LIMIT ' . $offset .',' . $limit;
    }

    

    public function generate($options){
        parent::generate($options);
        $statement = $this->generateSelect($options);
        $statement .= ' ';
        $statement .= $this->generateFrom($options);
        $statement .= ' ';
        $statement .= $this->generateLeftJoins($options);
        $statement .= ' ';
        $statement .= $this->generateInnerJoins($options);
        $statement .= ' ';
        $statement .= $this->generateWhere($options);
        $statement .= ' ';
        $statement .= $this->generateGroupBy($options);
        $statement .= ' ';
        $statement .= $this->generateOrderBy($options);
        $statement .= ' ';
        $statement .= $this->generateLimit($options);
        $statement .= ' ';
        
       return new \Pvik\Database\SQL\Statement\Statement($statement, $this->parameters);
    }
    
}