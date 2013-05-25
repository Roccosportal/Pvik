<?php
namespace Pvik\Database\SQL\Statement\Builder;

abstract class Generic {
 
    protected $parameters;
    
    
    protected function generateWhere($options){
        $where = isset($options['where']) ? $options['where'] : null;
        
        if(!$where || empty($where) || !isset($where['statement'])){
           return '';
        }
        
        
        $statement = 'WHERE ';
        $statement .=  $where['statement'];
        
        if(isset($where['parameters'])){
            foreach($where['parameters'] as $parameter) {
                $this->parameters[] = $parameter;
            }
        }
        
        return $statement;
    }
    
    public function generate($options){
         $this->parameters = array(); 
    }

}
