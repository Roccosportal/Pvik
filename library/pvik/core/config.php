<?php
namespace Pvik\Core;
class Config {
    
    public static $Config;
    
    public static function Load($Path){
        require($Path);
    }
    
}
?>
