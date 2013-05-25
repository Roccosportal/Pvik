<?php

namespace Pvik\Database\SQL;
use Pvik\Core\Config;
use Pvik\Core\Log;
/**
 * Runs sql statements.
 */
abstract class Manager {
      /**
     * Contains the current instance of a manager according to the selected database type.
     * @var Manager 
     */
    protected static $Instance = null;
    
    /**
     * Get the current instance of the sql manager
     * @return Manager 
     */
    public static function GetInstance(){
            if(self::$Instance == null){
                $adapterClassName  = \Pvik\Database\Adapter\Adapter::getAdapterClassName('Manager');
                if($adapterClassName){
                    self::$Instance = new $adapterClassName();
                }
                else {
                    throw new \Exception();
                }
            }

            return self::$Instance;
    }

     /**
     * Executes a statement.
     * @param string $QueryString
     * @return mixed 
     */
    abstract public function Execute($SqlStatement);
    
      /**
     *  Returns the last inserted id
     * @return mixed 
     */
    abstract public function GetLastInsertedId();
    
       /**
     *  Escapes a string.
     * @param string $String
     * @return string
     */
    abstract public  function EscapeString($String);
    
      /**
     *  Fetches an associative array from a database result
     * @param mixed $Result
     * @return array
     */
     abstract public  function FetchAssoc($Result);

  
     public function ExecuteStatement(Statement\Statement $statement){
        $ConvertedParameters = $this->ConvertParameters($statement->getParameters());
        $SqlStatement = vsprintf($statement->getStatement(), $ConvertedParameters);
        return $this->Execute($SqlStatement);
     }
     
   

    /**
     * Escape parameters.
     * @param array $Parameters
     * @return array 
     */
    public function ConvertParameters(array $Parameters) {
        $ConvertedParameters = array();
        foreach ($Parameters as $Parameter) {
            array_push($ConvertedParameters, $this->EscapeString(Type::convertValue($Parameter)));
        }
        return $ConvertedParameters;
    }
    
 
    

    
  

}