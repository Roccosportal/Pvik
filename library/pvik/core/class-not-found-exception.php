<?php

namespace Pvik\Core;
/**
 * Exception when no route was found.
 */
class ClassNotFoundException extends \Exception {
    
    protected $Class;
    
    protected $SearchedFor;


    public function __construct($Class, $SearchedFor) {
        $this->Class = $Class;
        $this->SearchedFor = $SearchedFor;
        $Message = 'Class not found: ' . $Class . "\n".'Searched for:' . $SearchedFor;
        parent::__construct($Message);
    }
    
    public function GetClass(){
        return $this->Class;
    }
    
    public function GetSearchedFor(){
        return $this->SearchedFor;
    }
    
    
    
}
