<?php

namespace Pvik\Core;

use Pvik\Core\Path;
use Pvik\Core\Config;
use Pvik\Web\RouteManager;
use Pvik\Web\ErrorManager;

/**
 * Contains the core functionalities
 */
class Core {

  
    /**
     * Initializes the web functionalities.
     * Initializes the error manager.
     * Starts the route manager.
     */
    public function StartWeb() {
        ErrorManager::Init();
        $RouteManager = new RouteManager();
        $RouteManager->Start();
    }

    /**
     * Loads the given configs into \Pvik\Core\Config.
     * If no config paths are given it loads the default path.
     * Passes the namespace associations to the class loaders
     * @param array $ConfigPaths
     * @return \Pvik\Core\Core
     */
    public function LoadConfig(array $ConfigPaths = array()) {
        if (empty($ConfigPaths)) {
            // set default config paths
            $ConfigPaths = array('~/application/configs/default-config.php', '~/application/configs/config.php');
        }
        foreach ($ConfigPaths as $ConfigPath) {
            Config::Load(Path::RealPath($ConfigPath));
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