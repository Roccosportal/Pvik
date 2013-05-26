<?php

namespace Pvik\Utils;

/**
 * Class that can contains the validation state and errors if occured.
 */
class ValidationState {

    /**
     * Indicates if this class has errors.
     * @var bool 
     */
    protected $valid;

    /**
     * Contains the errors.
     * @var KeyValueArray 
     */
    protected $errors;

    /**
     * 
     */
    public function __construct() {
        $this->valid = true;
        $this->errors = new KeyValueArray();
    }

    /**
     * Set an error for a field.
     * @param string $field
     * @param string $message 
     */
    public function setError($field, $message) {
        $this->valid = false;
        $this->errors->set($field, $message);
    }

    /**
     * Gets an error or null.
     * @param string $field
     * @return string 
     */
    public function getError($field) {
        return $this->errors->get($field);
    }

    /**
     * Checks if this objects contains errors.
     * @return bool 
     */
    public function isValid() {
        return $this->valid;
    }

}