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
    protected static $instance = null;
    
    /**
     * Get the current instance of the sql manager
     * @return Manager 
     */
    public static function getInstance(){
            if(self::$instance == null){
                $adapterClassName  = \Pvik\Database\Adapter\Adapter::getAdapterClassName('Manager');
                if($adapterClassName){
                    self::$instance = new $adapterClassName();
                }
                else {
                    throw new \Exception();
                }
            }

            return self::$instance;
    }

     /**
     * Executes a statement.
     * @param string $queryString
     * @return mixed 
     */
    abstract public function execute($sqlStatement);
    
      /**
     *  Returns the last inserted id
     * @return mixed 
     */
    abstract public function getLastInsertedId();
    
       /**
     *  Escapes a string.
     * @param string $string
     * @return string
     */
    abstract public  function escapeString($string);
    
      /**
     *  Fetches an associative array from a database result
     * @param mixed $result
     * @return array
     */
     abstract public  function fetchAssoc($result);

  
     public function executeStatement(Statement\Statement $statement){
        $convertedParameters = $this->convertParameters($statement->getParameters());
        $sqlStatement = vsprintf($statement->getStatement(), $convertedParameters);
        return $this->execute($sqlStatement);
     }
     
   

    /**
     * Escape parameters.
     * @param array $parameters
     * @return array 
     */
    public function convertParameters(array $parameters) {
        $convertedParameters = array();
        foreach ($parameters as $parameter) {
            
            array_push($convertedParameters, Type::getInstance()->convertValue($parameter));
        }
        return $convertedParameters;
    }
    
 
    

    
  

}