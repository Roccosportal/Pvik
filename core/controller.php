<?php

class Controller {

    protected $ViewData = null;
    protected $Parameters = null;

    public function  __construct($UrlParameters) {
        $this->Parameters = $UrlParameters;
        If($this->Parameters==null)
           $this->Parameters = new KeyValueArray();
        $this->ViewData = new KeyValueArray();
    }
    public function GetViewData(){
        return $this->ViewData;
    }

    protected  function ExecuteViewByAction($ActionName){
        Log::WriteLine('Redirecting to action view: ' . $ActionName );
        $ViewPath = ControllerManager::GetViewPathByAction($ActionName);
        if($ViewPath=="")
            throw new Exception('No view found in ~/views/~/' . ControllerManager::GetControllerName () . '/' . ControllerManager::GetActionName(). '.php');

        ViewManager::ExecuteView($ViewPath, $this);
    }

    protected function ExecuteView(){
        $ViewPath = ControllerManager::GetViewPath();
        if($ViewPath=="")
            throw new Exception('No view found in ~/views/~/' . ControllerManager::GetControllerName () . '/' . ControllerManager::GetActionName(). '.php');

        ViewManager::ExecuteView($ViewPath, $this);
    }

    protected function RedirectToController($ControllerName, $ActionName){
        Log::WriteLine('Redirecting to controller: ' .$ControllerName);
        Log::WriteLine('Redirecting to action: ' .$ActionName);
        $this->RedirectToControllerWithParameters($ControllerName, $ActionName, $this->Parameters);
    }

    protected function RedirectToControllerWithParameters($ControllerName, $ActionName, KeyValueArray $Parameters = null){
        ControllerManager::ExecuteController($ControllerName, $ActionName, $Parameters);
    }

    protected function RedirectToPath($Path){
        $RelativePath = Core::RelativePath($Path);
        header("Location: ". $RelativePath);
    }

   



    
}
?>