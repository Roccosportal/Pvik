<?php

namespace Pvik\Web;

use Pvik\Utils\KeyValueArray;
use Pvik\Web\ControllerManager;
use Pvik\Core\Config;
use Pvik\Core\Path;
use Pvik\Core\Log;

/**
 * This class contains the logic for a web site.
 */
class Controller {

    /**
     * Contains the data for the view.
     * @var KeyValueArray 
     */
    protected $viewData = null;

    /**
     * 
     * @var Request 
     */
    protected $request = null;

    /**
     * Name of the controller.
     * @var string 
     */
    protected $controllerName = null;

    /**
     * Name of the current executed action.
     * @var type 
     */
    protected $currentActionName = null;

    /**
     * 
     * @param \Pvik\Web\Request $request
     * @param string $controllerName
     */
    public function __construct(Request $request, $controllerName) {
        $this->request = $request;
        $this->controllerName = $controllerName;
        $this->viewData = new KeyValueArray();
    }

    /**
     * Sets the name of the current executed action
     * @param string $name
     */
    public function setCurrentActionName($name) {
        $this->currentActionName = $name;
    }

    /**
     * Returns the data for the view
     * @return KeyValueArray;
     */
    public function getViewData() {
        return $this->viewData;
    }

    /**
     * Execute a view.
     * @param string $actionName
     * @param string $folder
     */
    protected function executeViewByAction($actionName, $folder = null) {
        if ($folder == null) {
            $folder = Config::$config['DefaultViewsFolder'];
        }
        $viewPath = Path::realPath($folder . $this->controllerName . '/' . $actionName . '.php');
        \Pvik\Core\Log::writeLine('Executing view: ' . $viewPath);
        $view = new View($viewPath, $this);
    }

    /**
     * Execute a view.
     * @param string $folder
     */
    protected function executeView($folder = null) {
        $this->executeViewByAction($this->currentActionName, $folder);
    }

    /**
     * Redirects to another controllers action with passing the original parameters.
     * @param string $controllerName
     * @param string $actionName 
     */
    protected function redirectToController($controllerName, $actionName, Request $request = null) {
        if ($request == null) {
            $request = $this->request;
        }
        Log::writeLine('Redirecting to controller: ' . $controllerName);
        Log::writeLine('Redirecting to action: ' . $actionName);

        ControllerManager::executeController($controllerName, $actionName, $request);
    }

    /**
     * Redirect to a url via setting the location in the header.
     * @param string $path 
     */
    protected function redirectToPath($path) {
        $relativePath = Path::relativePath($path);
        header("Location: " . $relativePath);
    }

}
