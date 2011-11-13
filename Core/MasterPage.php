<?php

class MasterPage {
    protected $View = null;
    protected $MasterPagePath;
    protected $ViewData = null;

    public function __construct($MasterPagePath,View $View){
        $this->MasterPagePath = $MasterPagePath;

        // set the view that uses the masterpage
        $this->View = $View;
        $this->ViewData = $View->GetViewData();

        $this->ExecutePartialCode();
    }

    protected function ExecutePartialCode(){
        // delete old content and ignore it
        ob_get_clean();
        // start output buffering
        // the core will output the html
        ob_start();
         // include partial code
        require($this->MasterPagePath);
    }

    public function UseContent($ContentId){
        $Content = '';
        if($this->View!=null){
            $Contents = $this->View->GetContents();
            if(is_array($Contents) && isset($Contents[$ContentId])){
                $Content = $Contents[$ContentId];
            }
        }
        echo $Content;
    }
}

?>
