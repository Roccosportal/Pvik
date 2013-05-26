<?php

namespace Pvik\Utils;

/**
 * A class that assigns a key to a value.
 */
Class KeyValuePair {

    /**
     *
     * @var string 
     */
    protected $key = null;

    /**
     *
     * @var mixed 
     */
    protected $value = null;

    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Returns the key.
     * @return string 
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Sets the value.
     * @param mixed $value 
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * Returns the value.
     * @return mixed 
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Returns the value.
     * @return mixed. 
     */
    public function __toString() {
        return $this->value;
    }

}