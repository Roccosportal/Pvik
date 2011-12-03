<?php

class ViewManager {
    protected static $ViewPath = null;
    protected static $MasterPagePath = null;

    public static function ExecuteView($ViewPath, $Controller){
        Log::WriteLine('Executing view: '. $ViewPath);
        self::$ViewPath = $ViewPath;
        $BaseView = new View($ViewPath, $Controller);
    }

    public static function ExecuteMasterPage($MasterPagePath, $View){
        Log::WriteLine('Executing masterpage: '. $MasterPagePath);
        self::$MasterPagePath = $MasterPagePath;
        $BaseMasterPage = new MasterPage($MasterPagePath, $View);
    }
}

?>