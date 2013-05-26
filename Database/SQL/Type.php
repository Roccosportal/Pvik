<?php
namespace Pvik\Database\SQL;

class Type {
    
    protected static $instance;
    
    public static function getInstance(){
        if(!self::$instance){
            $adapterClassName  = \Pvik\Database\Adapter\Adapter::getAdapterClassName('SQL\Type');
            if($adapterClassName){
                self::$instance = new $adapterClassName();
            }
            else{
                self::$instance = new Type();
            }
        }
        return self::$instance;
    }
    
    protected $quoteSign = "'";
    
    protected function __construct(){
        
    }
    
    
     /**
     * Converts a value to a sql string.
     * @param mixed $value
     * @return string
     */
    public function convertValue($value) {
        if (is_bool($value)) {
            return $this->boolean($value);
        }
        elseif(is_array($value) || (is_object ($value)  && $value instanceof \ArrayObject)){
           return $this->arrayValue($value);
        }
        elseif ($value !== null) {
            return Manager::getInstance()->escapeString($value);
        } else {
            return $this->null();
        }
    }
    
    public function boolean($value){
        if ($value == true)
            return 'TRUE';
        else
            return 'FALSE';
    }
    
    public function null(){
       return 'NULL';
    }
    
    public function arrayValue($array){
        $first = true;
        $flatValue = '';
        foreach($array as $value){
            if (!$first) {
                $flatValue .= ',';
            }
            else {
                $first = false;
            }
            if(is_numeric($value)){
                $flatValue .= $value;
            }
            else {
                 $flatValue .= $this->quoteSign . Manager::getInstance()->escapeString($value) . $this->quoteSign;
            }
        }
        return $flatValue;
    }
}

