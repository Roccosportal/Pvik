<?php

class ControllerManager {
    protected static $ActionName = null; // contains the name of the current executed action
    protected static $ControllerName = null; // contain the name of the current executed controller

    public static function GetActionName(){
        return self::$ActionName;
    }

    public static function GetControllerName(){
        return self::$ControllerName;
    }

    public static function GetViewPath(){
        return self::GetViewPathByAction(self::$ActionName);
    }

    public static function GetViewPathByAction($ActionFileName){
        $FolderPath = Core::RealPath('~/Views');
        $Path = self::SearchForView($FolderPath, self::$ControllerName, $ActionFileName .'.php');
        Log::WriteLine('ViewPath: ' . $Path);
        return $Path;
    }

    protected static function SearchForView($FolderPath, $ControllerFolderName, $ActionFileName){
         $Path = '';
         if ($Handle = opendir($FolderPath)) {
            while (false !== ($SubFolder = readdir($Handle))) {
                 // it's a sub folder
                $SubFolderPath = $FolderPath . '/' . $SubFolder;
                if($SubFolder != '.' && $SubFolder != '..' && is_dir($SubFolderPath)){
                    // it is the controller folder
                    if($SubFolder==$ControllerFolderName){
                        $Path = $FolderPath . '/' .$ControllerFolderName . '/' . $ActionFileName;
                        // break search
                        Break;
                    }
                    else {
                        
                        $Search = self::SearchForView($SubFolderPath, $ControllerFolderName, $ActionFileName);
                        if($Search!=""){
                            // search found something
                            $Path = $Search;
                            Break;
                        }
                    }

                }
            }
        }
        return $Path;
    }

    public static function ExecuteController($ControllerName, $ActionName,KeyValueArray $Parameters = null){
        $ControllerClassName = $ControllerName . 'Controller';
        // save controller und action name
        self::$ActionName = $ActionName;
        self::$ControllerName = $ControllerName;

        Log::WriteLine('Executing controller: '. $ControllerName .', using '. $ControllerClassName . ' as class');
        
        if(class_exists($ControllerClassName)){
            // create a new instance
            $ControllerInstance = new $ControllerClassName($Parameters);
            if(method_exists($ControllerInstance, $ActionName)){
                Log::WriteLine('Executing action: '.$ActionName );
                // execute action
                $ControllerInstance->$ActionName();
            }
            else {
                throw new Exception('Action doesn\'t exists: '. $ControllerClassName . '->' . $ActionName);
            }
        }
        else {
            throw new Exception('Controller class doesn\'t exists: ' .$ControllerClassName);
        }
        
       
    }

}
?>