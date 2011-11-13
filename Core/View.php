<?php

class View {
    protected $MasterPagePath = null;
    protected $Contents = array();
    protected $CurrentContentId = null;
    protected $Controller = null;
    protected $ViewData = null;
    protected $ViewPath = null;

    public function __construct($ViewPath,Controller $Controller){
        $this->ViewPath = $ViewPath;
        $this->Controller = $Controller;

        $this->ViewData = $this->Controller->GetViewData();

        $this->ExecutePartialCode();
        
        If($this->MasterPagePath != null){
            ViewManager::ExecuteMasterPage(Core::RealPath($this->MasterPagePath), $this);
        }
    }

    protected function ExecutePartialCode(){
        if(!file_exists($this->ViewPath)){
            throw new Exception('View file doesn\'t exist: '. $this->ViewPath);
        }
        // include partial code
        require($this->ViewPath);
    }

    protected function UseMasterPage($MasterPagePath){
        $this->MasterPagePath = $MasterPagePath;
    }

    protected function StartContent($ContentId) {
        if($this->MasterPagePath!=null){
            // delete old content that is outside of the content tags
            ob_get_clean();
            // set the content id and start getting all output content
            $this->CurrentContentId = $ContentId;
            ob_start();
        }
    }


    protected function EndContent(){
        if($this->MasterPagePath!=null && $this->CurrentContentId!=null){
            // save the output content in a array to pass it to the masterpage
            $this->Contents[$this->CurrentContentId] = ob_get_clean();
            $this->CurrentContentId = null;
            // start output buffering
            // if we use a masterpage every content have to be between content tags
            // we buffer contents outside of tags to ignore it
            ob_start();
        }
    }

    public function GetContents(){
        return $this->Contents;
    }

    public function GetViewData(){
        return $this->ViewData;
    }

}

?>
