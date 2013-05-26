<?php

namespace Pvik\Core;

/**
 * Exception when no class was found.
 */
class ClassNotFoundException extends \Exception {

    /**
     * The class that was looked for 
     * @var string 
     */
    protected $class;

    /**
     * The path that was looked in
     * @var string 
     */
    protected $searchedFor;

    /**
     * 
     * @param string $class The class that was looked for
     * @param string $searchedFor The path that was looked in
     */
    public function __construct($class, $searchedFor) {
        $this->class = $class;
        $this->searchedFor = $searchedFor;
        $message = 'Class not found: ' . $class . "\n" . 'Searched for:' . $searchedFor;
        parent::__construct($message);
    }

    /**
     * Returns the class that was looked for
     * @return string
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * Returns the path that was looked in
     * @return type
     */
    public function getSearchedFor() {
        return $this->searchedFor;
    }

}