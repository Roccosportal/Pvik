<?php

namespace Pvik\Web;
class Request {
    
    protected $Url;
    
    protected $Parameters;
    
    protected $Route;
    
    protected $CurrentController;
   
    public function __construct(){
        $this->Parameters = new \Pvik\Utils\KeyValueArray();

    }
    
    public function GetUrl(){
        return $this->Url;
    }
    
    public function SetUrl($Url){
        $this->Url = $Url;
    }
    
    public function SetRoute($Route){
        $this->Route = $Route;
    }
    
    public function GetRoute(){
        return $this->Route;
    }
    
    /**
     * @return \Pvik\Utils\KeyValueArray
     */
    public function GetParameters(){
        return $this->Parameters;
    }
    
    /**
     * Returns a $_POST value or null.
     * @param string $Key
     * @return string 
     */
    public function GetPOST($Key) {
        if ($this->IsPOST($Key)) {
            return $_POST[$Key];
        }
        return null;
    }

    /**
     * Checks if a $_POST value is set.
     * @param string $Key
     * @return string 
     */
    public function IsPOST($Key) {
         return isset($_POST[$Key]);
    }
    
    public function IsGET($Key){
        return isset($_GET[$Key]);
    }

    /**
     * Returns a $_GET value or null.
     * @param string $Key
     * @return string 
     */
    public function GetGET($Key) {
        if ($this->IsGET($Key)) {
            return $_GET[$Key];
        }
        return null;
    }
    
    
    /**
     * Is set to true if a sessions was started.
     * @var type 
     */
    protected static $SessionStarted = false;

    /**
     * Starts a session if not already started.
     * Use this function to prevent multiple session starts.
     */
    public function SessionStart() {
        if (!self::$SessionStarted) {
            session_start();
            self::$SessionStarted = true;
        }
    }
       
}
