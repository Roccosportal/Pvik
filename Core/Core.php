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
    public function startWeb() {
        ErrorManager::init();
        $routeManager = new RouteManager();
        $routeManager->start();
    }

    /**
     * Loads the given configs into \Pvik\Core\Config.
     * @param array $configPaths
     * @return \Pvik\Core\Core
     */
    public function loadConfig(array $configPaths) {
        foreach ($configPaths as $configPath) {
            Config::load(Path::realPath($configPath));
        }
        Log::writeLine('[Info] Loaded: ' . implode(",", $configPaths));
        return $this;
    }

    /**
     * Creates an guid.
     * @return string 
     */
    public static function createGuid() {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $charId = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = chr(123)// "{"
                    . substr($charId, 0, 8) . $hyphen
                    . substr($charId, 8, 4) . $hyphen
                    . substr($charId, 12, 4) . $hyphen
                    . substr($charId, 16, 4) . $hyphen
                    . substr($charId, 20, 12)
                    . chr(125); // "}"
            return $uuid;
        }
    }

}
