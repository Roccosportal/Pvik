<?php
namespace Pvik\Database\SQL\Statement\Builder;

class Insert extends Generic{
    protected static $Instance;
    
   public static function getInstance(){
        if(!self::$Instance){
            $adapterClassName  = \Pvik\Database\Adapter\Adapter::getAdapterClassName('SQL\Statement\Builder\Insert');
            if($adapterClassName){
                self::$Instance = new $adapterClassName();
            }
            else{
                self::$Instance = new Insert();
            }
        }
        return self::$Instance;
    }
    
    protected function __construct(){
        
    }
    
   
    
    protected function generateInsert($options){
        $table = isset($options['table']) ? $options['table'] : null;
        
        if(!$table){
            throw new \Exception();
        }
        
        return 'INSERT INTO ' . $table;
    }
    
    protected function generateSet($options){
        $fields = isset($options['fields']) ? $options['fields'] : null;
        $quoteSign = isset($options['quoteSign']) ? $options['quoteSign'] : '"';
        
        if(!$fields || count($fields) == 0 ){
            $fields = array('*');
        }
        $statement = '( ';
        
        $first = true;
        foreach($fields as $field){
             if (!$first) {
                // add , at the end
                $statement .= ',';
             }
             else {
                 $first = false;
             }
             $statement .= $field['field'];
        }
        $statement .= ') VALUES (';
        
        $first = true;
        foreach($fields as $field){
             if (!$first) {
                // add , at the end
                $statement .= ',';
             }
             else {
                 $first = false;
             }
             $statement .=  $quoteSign .'%s' . $quoteSign ;
             $this->parameters[] = $field['value'];
        }
        $statement .= ')';
        return $statement;
    }
    
 
   
    public function generate($options){
        parent::generate($options);
        $statement = '';
        $statement .= $this->generateInsert($options);
        $statement .= ' ';
        $statement .= $this->generateSet($options);
        $statement .= ' ';
       return new \Pvik\Database\SQL\Statement\Statement($statement, $this->parameters);
        
    }
    
    
}

?>
