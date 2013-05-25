<?php
namespace Pvik\Database\SQL\Statement;
class Statement {
    
    protected $statement;
    
    protected $parameters;
    
    public function __construct($statement, array $parameters){
        $this->statement = $statement;
        $this->parameters = $parameters;
    }

    public function getStatement(){
        return $this->statement;
    }
    
    public function getParameters(){
        return $this->parameters;
    }
    
    
    
    
}
