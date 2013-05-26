<?php
namespace Pvik\Database\SQL\Statement\Builder;

class Delete extends Generic{
    protected static $instance;
    
    public static function getInstance(){
        if(!self::$instance){
            $adapterClassName  = \Pvik\Database\Adapter\Adapter::getAdapterClassName('SQL\Statement\Builder\Delete');
            if($adapterClassName){
                self::$instance = new $adapterClassName();
            }
            else{
                self::$instance = new Delete();
            }
        }
        return self::$instance;
    }
    
    protected function __construct(){
        
    }
    
   
    
    protected function generateDelete($options){
        $table = isset($options['table']) ? $options['table'] : null;
        
        if(!$table){
            throw new \Exception();
        }
        
        return 'DELETE FROM ' . $table;
    }
    
  
    
 
   
    public function generate($options){
        parent::generate($options);
        $statement = '';
        $statement .= $this->generateDelete($options);
        $statement .= ' ';
        $statement .= $this->generateWhere($options);
        $statement .= ' ';
       return new \Pvik\Database\SQL\Statement\Statement($statement, $this->parameters);
        
    }
    
    
}
