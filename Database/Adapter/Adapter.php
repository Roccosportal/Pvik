<?php
namespace Pvik\Database\Adapter;
use Pvik\Core\Config;

class Adapter {
    
    
   public static function getAdapterClassName($className){
        if (!isset(Config::$config['Database']) || !isset(Config::$config['Database']['Path'])) {
           return null;
          
        }
        $className =Config::$config['Database']['Path'] . $className;
            
        if(!class_exists($className)){
               return null;
        }
        return $className;   

   }
}

