<?php
error_reporting(E_ALL ^ E_NOTICE);
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");
Class Core {

    public static $Url = null;
    public static $UrlParameters = null; // used as KeyValueArray
    public static $Config = null;
    protected static $RelativeFileBase;
    protected static $AbsoluteFileBase;

    public function __construct() {
        try {
            
            // get the file base
            $RequestUri = $_SERVER['REQUEST_URI'];
            $RelativeFileBase = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) ;

            self::$RelativeFileBase = $RelativeFileBase;
            self::$AbsoluteFileBase =  $_SERVER['DOCUMENT_ROOT'] . $RelativeFileBase;

            // load the config
            $this->LoadConfig();
           

            Log::WriteLine('Relative file base: ' . self::$RelativeFileBase);
            Log::WriteLine('Absolute file base: ' . self::$AbsoluteFileBase);

            if(self::$Config['UnderConstruction']['Enabled']==true){
                $this->ExecuteUnderConstruction(Core::RealPath(self::$Config['UnderConstruction']['Path']));
            }
            else {

                // include files
                $this->IncludeFolders();

                $Route = $this->GetRoute();
                if ($Route != null) {
                    // start output buffering
                    ob_start();
                    // execute controller
                    ControllerManager::ExecuteController($Route['Controller'], $Route['Action'], self::$UrlParameters);
                    // end output buffering and output the buffer
                    echo ob_get_clean();

                }
                else {
                    throw new NoRouteFoundException('No route found for '. self::$Url);
                }
            }



        }
        catch (Exception $Exception) { // unhandled exception
            // delete output buffer and ignore it
            ob_get_clean();
            $this->ErrorPage($Exception);
        }
    }

    protected function ErrorPage($Exception){
        try {
            $ExceptionClass = get_class($Exception);
            $ErrorPages = self::$Config['ErrorPages'];
            if(isset($ErrorPages[$ExceptionClass])){
                $File = Core::RealPath($ErrorPages[$ExceptionClass]);
                if(file_exists($File)){
                    $this->ExecuteErrorPage($Exception, $File);
                }
                else {
                    throw new Exception('Erropage '. $File. ' not found');
                }
            }
            else {
                $File = Core::RealPath($ErrorPages['Default']);
                if(file_exists($File)){
                    $this->ExecuteErrorPage($Exception, $File);
                }
                else {
                    throw new Exception('Erropage '. $File. ' not found');
                }
            }
            
        }
        catch(Exception $ex){
            echo $ex->getMessage();
        }
    }

    protected function ExecuteErrorPage($Exception, $File){
        require($File);
    }

    protected function ExecuteUnderConstruction($File){
        require($File);
    }


     protected function GetRoute() {
        $RouteMatch = null;


        $Url = substr($_SERVER['REQUEST_URI'], strlen(self::$RelativeFileBase));
        // Delete Parameters
        $QueryStringPos = strpos($Url, '?');
        If($QueryStringPos!== false){
            $Url = substr($Url,0,  $QueryStringPos);
        }
        $Url = strtolower($Url);
        if(strlen($Url)!=0){
            // add a / at the start if not already has
            if ($Url[0] != '/')
                $Url = '/' . $Url;

            // add a / at the end if not already has
            if ($Url[strlen($Url) - 1] != '/')
                $Url = $Url . '/';
        }
        else {
            $Url = '/';
        }
       
        self::$Url = $Url;
        Log::WriteLine('Url: ' . $Url);

        // set to array because if a route matches the variable automatically get filled
        self::$UrlParameters = new KeyValueArray();
        $Routes = self::$Config['Routes'];
        foreach ($Routes as $Route) {
            if ($this->UrlIsMatching($Url,$Route) ) {
                Log::WriteLine('Route matching: ' . $Route['Url']);
                $RouteMatch = $Route;
                break;
            }
        }
        return $RouteMatch;
    }

    protected function UrlIsMatching($OrignalUrl, $Route){
        $RouteUrl = $Route['Url'];
        $IsMatching = false;
        if($OrignalUrl == $RouteUrl){
            return true;
        }
        elseif (strpos($RouteUrl, '{') !== false && strpos($RouteUrl, '}') !== false) // contains a variable
        {
            $RouteUrlParts = explode('/', $RouteUrl);
            $OrignalUrlParts = explode('/', $OrignalUrl);
            // have the same part length
            if(count($RouteUrlParts) == count($OrignalUrlParts)){
               for ($Index = 0; $Index < count($RouteUrlParts); $Index++) {
                   if(strlen($RouteUrlParts[$Index]) >= 3 && $RouteUrlParts[$Index][0]=='{'){ // it's a variable 
                        $Key = substr($RouteUrlParts[$Index], 1, -1);
                        if(isset($Route['Parameters'][$Key]) &&  !preg_match($Route['Parameters'][$Key], $OrignalUrlParts[$Index])){
                            return false;
                        }
             
                   }
                   else if($RouteUrlParts[$Index] != $OrignalUrlParts[$Index]) {
                       // not matching
                       return false;
                   }
               }
               // matching successfull
               // save url parameter
               for ($Index = 0; $Index < count($RouteUrlParts); $Index++) {
                   if(strlen($RouteUrlParts[$Index]) >= 3 && $RouteUrlParts[$Index][0]=='{'){ // it's a variable
                        // the key is the name between the brakets
                        $Key = substr($RouteUrlParts[$Index], 1, -1);
                        // add to url parameters
                        self::$UrlParameters->add($Key ,$OrignalUrlParts[$Index]);
                        if(isset($Route['Parameters'][$Key])){
                            Log::WriteLine('Url parameter: '. $Key. ' -> ' . $Route['Parameters'][$Key] . ' -> ' .  $OrignalUrlParts[$Index]);
                        }
                        else {
                            Log::WriteLine('Url parameter: '. $Key. ' -> ' . $OrignalUrlParts[$Index]);
                        }
                   }
               }
               return true;
            }
        }
        return false;
    }

    protected function LoadConfig() {
        self::$Config = array();

        // load at first default values
        require(Core::RealPath('~/Configs/DefaultConfig.php'));

        // overwrite them
        require(Core::RealPath('~/Configs/Config.php'));
        Log::WriteLine('Loaded: Config.php');
    }



    protected function IncludeFolders() {
        foreach (self::$Config['IncludeFolders'] as $FolderPath) {
            $RealFolderPath = Core::RealPath($FolderPath);
            $this->IncludeFolder($RealFolderPath, 0);
        }
    }

    public function IncludeFolder($RealFolderPath) {
        
        if ($Handle = opendir($RealFolderPath)) {
            while (false !== ($File = readdir($Handle))) {
                $Extension = '';
                $Path = $RealFolderPath . '/' . $File;
                If (strlen($File) > 4) {
                    // get extension from file
                    $Extension = substr($File, -4);
                }
                if ($Extension == '.php') {
                    Log::WriteLine('Including: ' . $Path . ' (if not already is included)');

                    include_once($Path);
                   
                } elseif ($File != '.' && $File != '..' && is_dir($Path)) {
                    // it's a sub folder
                    $this->IncludeFolder($Path);
                }
            }
            closedir($Handle);
        }
    }
    public static function Depends($FilePath){
        $FilePath = self::RealPath($FilePath);
        Log::WriteLine('Including: ' . $FilePath . ' (if not already is included)');
        include_once($FilePath);
    }

    public static function RealPath($Path) {
        $NewFilePath = str_replace('~/', self::$AbsoluteFileBase, $Path);
        return $NewFilePath;
    }

    public static function RelativePath($Path) {
        $NewPath = str_replace('~/', self::$RelativeFileBase, $Path);
        return $NewPath;
    }

    public static function GetPOST($Key){
        $Data = null;
        if(isset($_POST[$Key])){
            $Data = $_POST[$Key];
        }
        return $Data;
    }

    public static function IsPOST($Key){
        if(isset($_POST[$Key])){
            return true;
        }
        else {
            return false;
        }
    }

    public static function GetGET($Key){
        $Data = null;
        if(isset($_GET[$Key])){
            $Data = $_GET[$Key];
        }
        return $Data;
    }


}

Class Log {

    protected static $Log = null;
    protected $LogTrace;
    protected $LogFileHandle = null;

    public function __construct() {
        $this->LogTrace = array();
        $date = new DateTime();
        if (Core::$Config['Log']['UseOneFile'] == false) {
            $GeneratedFilePath = Core::RealPath('~/Logs') . '/Log_' . $date->getTimestamp() . '.txt';
        } else {
            $GeneratedFilePath = Core::RealPath('~/Logs/Log_file.txt');
        }

        $this->LogFileHandle = fopen($GeneratedFilePath, 'w');
    }

    public function Write($Message) {
        If ($this->LogFileHandle != null){
            fwrite($this->LogFileHandle, $Message);
        }
        array_push($this->LogTrace, $Message);
    }

    public static function WriteLine($Message) {
        if (Core::$Config['Log']['On'] == true) {
            If (self::$Log == null) {
                self::$Log = new Log();
            }
            self::$Log->Write($Message . "\n");
        }
    }

    public static function GetTrace(){
        $TraceString = "";
        If (self::$Log != null) {
             $TraceString = self::$Log->GetTraceString();
        }
        return $TraceString;
    }

    public function GetTraceString(){
        $TraceString = "";
        $Max = count($this->LogTrace);
        if ($Max > 0){
            for ($i = 0; $i < $Max; $i++) {
                if(($i + 1)< 10){
                    $TraceString .= '#0' . ($i+ 1) . ' ' . $this->LogTrace[$i];
                }
                else {
                    $TraceString .= '#' . ($i + 1). ' ' . $this->LogTrace[$i];
                }
            }
        }
        return $TraceString;
    }

    public function __destruct() {
        if ($this->LogFileHandle != null) {
            fclose($this->LogFileHandle);
        }
    }

}

Class KeyValueArray {
    protected $KeyValuePairs = null;


    public function  __construct() {
        $this->KeyValuePairs = array();
    }


    public function Add($Key, $Value){
        if(!$this->ContainsKey($Key)){
            $KeyValuePair = new KeyValuePair($Key, $Value);
            array_push($this->KeyValuePairs, $KeyValuePair);
        }
        else {
            throw new Exception('The key already exists: '. $Key);
        }
    }

    public function Set($Key, $Value){
        if($this->ContainsKey($Key)){
            $Pair = $this->GetPair($Key);
            if($Pair!=null){
                $Pair->SetValue($Value);
            }
            else {
                 throw new Exception('Unexpected error caused.');
            }
        }
        else {
            $this->Add($Key, $Value);
        }

    }

    public function Remove($Key){
        if($this->ContainsKey($Key)){
            unset($this->KeyValuePairs[$Key]);
        }
        else {
            throw new Exception('The key doesn\'t exists: '. $Key);
        }
    }

    public function GetPair($Key){
        foreach( $this->KeyValuePairs as $KeyValuePair){
            if($KeyValuePair->GetKey()==$Key){
                return $KeyValuePair;
            }
        }
        return null;
    }

    public function Get($Key){
        foreach( $this->KeyValuePairs as $KeyValuePair){
            if($KeyValuePair->GetKey()==$Key){
                return $KeyValuePair->GetValue();
            }
        }
        return null;
    }

    public function ContainsKey($Key){
        foreach($this->KeyValuePairs as $KeyValuePair ){
            if($KeyValuePair->GetKey()==$Key){
                return true;
            }
        }
        return false;
    }

    public function IsNotEmpty($Key){
        foreach($this->KeyValuePairs as $KeyValuePair ){
            if($KeyValuePair->GetKey()==$Key){
                $Value = $KeyValuePair->GetValue();
                if(!empty($Value)){
                    return true;
                }
                else {
                    return false;
                }
            }
        }
        return false;
    }

}

Class KeyValuePair{
    protected $Key = null;
    protected $Value = null;

    public function __construct($Key, $Value){
        $this->Key = $Key;
        $this->Value = $Value;
    }

    public function GetKey(){
        return $this->Key;
    }

    public function SetValue($Value){
        $this->Value = $Value;
    }

    public function GetValue(){
        return $this->Value;
    }

    public function  __toString() {
        return $this->Value;
    }
}
class NoRouteFoundException extends Exception { }
?>
