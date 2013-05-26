<?php
namespace Pvik\Database\SQL\Statement\Builder;

class Update extends Generic{
    protected static $instance;
    
     public static function getInstance(){
        if(!self::$instance){
            $adapterClassName  = \Pvik\Database\Adapter\Adapter::getAdapterClassName('SQL\Statement\Builder\Update');
            if($adapterClassName){
                self::$instance = new $adapterClassName();
            }
            else{
                self::$instance = new Update();
            }
        }
        return self::$instance;
    }
    
    protected function __construct(){
        
    }
    
   
    
    protected function generateUpdate($options){
        $table = isset($options['table']) ? $options['table'] : null;
        
        if(!$table){
            throw new \Exception();
        }
        
        return 'UPDATE ' . $table;
    }
    
    protected function generateSet($options){
        $fields = isset($options['fields']) ? $options['fields'] : null;
        $quoteSign = isset($options['quoteSign']) ? $options['quoteSign'] : '"';
        
        if(!$fields || count($fields) == 0 ){
            $fields = array('*');
        }
        $statement = 'SET ';
        
        $first = true;
        foreach($fields as $field){
             if (!$first) {
                // add , at the end
                $statement .= ', ';
             }
             else {
                 $first = false;
             }
             $statement .= $field['field'] . '='. $quoteSign .'%s' . $quoteSign ;
             $this->parameters[] = $field['value'];
        }
        return $statement;
    }
    
 
   
    public function generate($options){
        parent::generate($options);
         $statement = '';
        $statement .= $this->generateUpdate($options);
        $statement .= ' ';
        $statement .= $this->generateSet($options);
        $statement .= ' ';
        $statement .= $this->generateWhere($options);
        $statement .= ' ';
     
        
       return new \Pvik\Database\SQL\Statement\Statement($statement, $this->parameters);
        
    }
    
    
}

?>
