<?php
class ValidationState {
    protected $Valid;
    protected $Errors;

    public function __construct(){
        $this->Valid = true;
        $this->Errors = new KeyValueArray();
    }

    public function SetError($Field, $Message){
        $this->Valid = false;
        $this->Errors->Set($Field, $Message);
    }


    public function GetError($Field){
        return $this->Errors->Get($Field);
    }

    public function IsValid(){
        return $this->Valid;
    }
}
?>