<?php

namespace Pvik\Web;

use Pvik\Utils\KeyValueArray;
use Pvik\Core\Log;
use Pvik\Core\Path;


/**
 * Class that contains a partial view code.
 */
class View {

    /**
     * Contains the path to a master page if set up.
     * @var string 
     */
    protected $masterPagePath = null;

    /**
     * Contains the differnt areas of content if the view uses a master page.
     * @var KeyValueArray 
     */
    protected $contents;

    /**
     * Contains the id of the current content area.
     * @var string 
     */
    protected $currentContentId = null;

    /**
     * Contains the controller that executed this view.
     * @var Controller 
     */
    protected $controller = null;

    /**
     * Contains the view data from the controller
     * @var type 
     */
    protected $viewData = null;

    /**
     * Contains the path to the partial view
     * @var type 
     */
    protected $viewPath = null;

    /**
     * Contains the Html Helper.
     * @var ViewHelpers\HtmlHelper
     */
    protected $helper = null;

    /**
     *
     * @param string $viewPath
     * @param Controller $controller 
     */
    public function __construct($viewPath, Controller $controller) {
        $this->contents = new KeyValueArray();
        $this->viewPath = $viewPath;
        $this->controller = $controller;

        $this->viewData = $this->controller->getViewData();
        $this->helper = new \Pvik\Web\ViewHelpers\HtmlHelper();
        $this->executePartialCode($this->viewPath);

        if ($this->masterPagePath != null) {
            Log::writeLine('Executing masterpage: ' . $this->masterPagePath);
            $baseMasterPage = new MasterPage($this->realPath($this->masterPagePath), $this);
        }
    }

    /**
     * Executes the partial view.
     */
    protected function executePartialCode($viewPath) {
        if (!file_exists($viewPath)) {
            throw new \Exception('View file doesn\'t exist: ' . $viewPath);
        }
        // include partial code
        require($viewPath);
    }

    /**
     * Defines the master page.
     * @param type $masterPagePath 
     */
    protected function useMasterPage($masterPagePath) {
        $this->masterPagePath = $masterPagePath;
    }

    /**
     * Starts to fetch a content area.
     * @param type $contentId 
     */
    protected function startContent($contentId) {
        if ($this->masterPagePath != null) {
            // delete old content that is outside of the content tags
            ob_get_clean();
            // set the content id and start getting all output content
            $this->currentContentId = $contentId;
            ob_start();
        }
    }

    /**
     * Ends to fetch a content area and safes it into the Contents array.
     */
    protected function endContent() {
        if ($this->masterPagePath != null && $this->currentContentId != null) {
            // save the output content in a array to pass it to the masterpage
            $this->contents->set($this->currentContentId, ob_get_clean());
            $this->currentContentId = null;
            // start output buffering
            // if we use a masterpage every content have to be between content tags
            // we buffer contents outside of tags to ignore it
            ob_start();
        }
    }

    /**
     * Returns the content areas if the view used a master page.
     * @return KeyValueArray
     */
    public function getContents() {
        return $this->contents;
    }

    /**
     * Returns the view data.
     * @return KeyValueArray 
     */
    public function getViewData() {
        return $this->viewData;
    }

    /**
     * Shortcut to Path::relativePath function
     * @param string $path
     * @return string
     */
    protected function relativePath($path) {
        return Path::relativePath($path);
    }

    /**
     * Shortcut to Path::realPath function
     * @param string $path
     * @return string
     */
    protected function realPath($path) {
        return Path::realPath($path);
    }

}
