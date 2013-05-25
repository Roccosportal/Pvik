<?php
namespace Pvik\Database\Adapter;
use Pvik\Core\Config;

class Adapter {
    
    
   public static function getAdapterClassName($className){
        if (!isset(Config::$Config['Database']) || !isset(Config::$Config['Database']['Path'])) {
           return null;
          
        }
        $className =Config::$Config['Database']['Path'] . $className;
            
        if(!class_exists($className)){
               return null;
        }
        return $className;   

   }
}

