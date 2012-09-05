<?php

namespace Pvik\Core;
use Pvik\Core\Path;
class ClassLoader {
    protected static $Instance;
    
    protected $NamespaceAssociationList;
    
    protected function __construct(){
        $this->NamespaceAssociationList = array();
    }
    
    public function Init() {
        
        $this->Autoload();
    }
    
    /**
     * 
     * @return \Pvik\Core\ClassLoader
     */
    public static function GetInstance(){
        if(self::$Instance===null){
            self::$Instance = new ClassLoader();
        }
        return self::$Instance;
    }
    
    
    
    protected function Autoload(){
        spl_autoload_register(function ($Class) {
                if($Class[0]!== '\\'){
                    $Class = '\\' . $Class;
                }
                
                $Instance = ClassLoader::GetInstance();
                // TODO: optimize
                $Name = $Class;
                foreach($Instance->GetNamespaceAssociationList() as $Namespace => $Path){
                    if(strpos($Name, $Namespace) === 0){ // starts with
                        $Name = str_replace($Namespace, $Path, $Name);
                        break;  
                    }
                }
                
                $Path = Path::ConvertNameToPath($Name);
                $Path = str_replace('//', '/', $Path);
                $Path = Path::RealPath($Path . '.php');
                //echo $Path;
                if(file_exists($Path)){
                    
                    require $Path;
                    if(class_exists('\\Pvik\\Core\\Log')){
                        Log::WriteLine('[Include] ' . $Path);
                    }
                    return true;
                }
                else {
                    throw new \Pvik\Core\ClassNotFoundException($Class, $Path);
                }
            });
    }
  
    public function GetNamespaceAssociationList(){
        return $this->NamespaceAssociationList;
    }
    
    public function SetNamespaceAssociation($Namespace, $Path){
        $this->NamespaceAssociationList[$Namespace] = $Path;
        return $this;
    }
}
