<?php
namespace Pvik\Core;
use Pvik\Core\Path;
use Pvik\Core\ClassLoader;
use Pvik\Core\Config;
use Pvik\Web\RouteManager;
use Pvik\Web\ErrorManager;
Class Core {

    
    
    public function __construct() {
        
    }
    
  
    
   
    
    public function Init() {    
            date_default_timezone_set('UTC');
            
            
            
            // register class loader
            $CorePath = dirname(__FILE__) . '/';
            require $CorePath . 'class-loader.php';
            require $CorePath . 'path.php';
            require $CorePath . 'config.php';
            $this->ClassLoader = ClassLoader::GetInstance()->Init();
            Path::Init();
            return $this;   
    }

    
    public function StartWeb(){
            ErrorManager::Init();
            $RouteManager = new RouteManager();
            $RouteManager->Start();
            
    }
    
   

    /**
     * Loads first first the default config and then the config.
     */
    public function LoadConfig($ConfigPaths = array()) {
        if(empty($ConfigPaths)){
            // set default config paths
            $ConfigPaths = array('~/application/configs/default-config.php', '~/application/configs/config.php');
        }
        foreach ($ConfigPaths as $ConfigPath){
            Config::Load(Path::RealPath($ConfigPath));
        }
        if(isset(Config::$Config['NamespaceAssociations']) && is_array(Config::$Config['NamespaceAssociations'])){
            foreach(Config::$Config['NamespaceAssociations'] as $Namespace => $Path){
                ClassLoader::GetInstance()->SetNamespaceAssociation($Namespace, $Path);
            }
        }
        
        Log::WriteLine('[Info] Loaded: ' . implode(",", $ConfigPaths));
        return $this;
    }


  


    /**
     * Creates an guid.
     * @return string 
     */
    public static function CreateGuid() {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $CharId = strtoupper(md5(uniqid(rand(), true)));
            $Hyphen = chr(45); // "-"
            $Uuid = chr(123)// "{"
                    . substr($CharId, 0, 8) . $Hyphen
                    . substr($CharId, 8, 4) . $Hyphen
                    . substr($CharId, 12, 4) . $Hyphen
                    . substr($CharId, 16, 4) . $Hyphen
                    . substr($CharId, 20, 12)
                    . chr(125); // "}"
            return $Uuid;
        }
    }




}