<?php
namespace Pvik\Web;
/**
 * Class for a master page view.
 */
class MasterPage {
    /**
     * Contains the current view.
     * @var View 
     */
    protected $view = null;
    /**
     * Contains the path to the master page view.
     * @var string 
     */
    protected $masterPagePath;
    /**
     * Contains the view data from the view.
     * @var \Pvik\Utils\KeyValueArray 
     */
    protected $viewData = null;
    
    /**
     * Contains the Html Helper.
     * @var ViewHelpers\HtmlHelper
     */
    protected $helper = null;

    /**
     *
     * @param string $masterPagePath
     * @param View $view 
     */
    public function __construct($masterPagePath,View $view){
        $this->masterPagePath = $masterPagePath;

        // set the view that uses the masterpage
        $this->view = $view;
        $this->viewData = $view->getViewData();
        $this->helper = new \Pvik\Web\ViewHelpers\HtmlHelper();

        $this->executePartialCode();
    }

    /**
     * Execute the partial master page view. Should be ran after the normal views ran.
     */
    protected function executePartialCode(){
        // delete old content and ignore it
        ob_get_clean();
        // start output buffering
        // the core will output the html
        ob_start();
         // include partial code
        require($this->masterPagePath);
    }
    
    /**
     * Get the content from a normal view that was executed before.
     * @param string $contentId 
     */
    public function useContent($contentId){
        $content = '';
        if($this->view!=null){
            $contents = $this->view->getContents();
            $content = $contents->get($contentId);
            if($content==null){
                $content = '';
            }
        }
        echo $content;
    }
}

