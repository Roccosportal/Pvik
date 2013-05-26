<?php

namespace Pvik\Web;

use Pvik\Utils\KeyValueArray;
use Pvik\Core\Log;
use Pvik\Core\Config;

/**
 * This static class starts a controller.
 */
class ControllerManager {

    /**
     * Execute a action from a controller.
     * @param string $controllerName
     * @param string $actionName
     * @param KeyValueArray $parameters 
     */
    public static function executeController($controllerName, $actionName, Request $request) {
        $controllerClassName = $controllerName;
        if ($controllerClassName[0] !== '\\') {
            $controllerClassName = Config::$config['DefaultNamespace'] . Config::$config['DefaultNamespaceControllers'] . '\\' . $controllerClassName;
        }

        $controllerInstance = new $controllerClassName($request, $controllerName);
        /* @var $controllerInstance \Pvik\Web\Controller */
        $actionFunctionName = $actionName . 'Action';
        if (method_exists($controllerInstance, $actionFunctionName)) {
            Log::writeLine('Executing action: ' . $actionFunctionName);
            $controllerInstance->setCurrentActionName($actionName);
            // execute action    
            $controllerInstance->$actionFunctionName();
        } else {
            throw new \Exception('Action doesn\'t exists: ' . $controllerClassName . '->' . $actionFunctionName);
        }
    }

}