<?php

namespace Pvik\Utils;

/**
 * A simple associative array
 */
Class KeyValueArray {

    /**
     * Array of KeyValuePairs.
     * @var array 
     */
    protected $keyValuePairs = null;

    /**
     * 
     */
    public function __construct() {
        $this->keyValuePairs = array();
    }

    /**
     * Add a value to a key if the key doesn't exists.
     * @param string $key
     * @param mixed $value 
     */
    public function add($key, $value) {
        if (!$this->containsKey($key)) {
            $keyValuePair = new KeyValuePair($key, $value);
            array_push($this->keyValuePairs, $keyValuePair);
        } else {
            throw new \Exception('The key already exists: ' . $key);
        }
    }

    /**
     * Set a value to a key even when the key already exists.
     * @param string $key
     * @param mixed $value 
     */
    public function set($key, $value) {
        if ($this->containsKey($key)) {
            $pair = $this->getPair($key);
            if ($pair != null) {
                $pair->setValue($value);
            } else {
                throw new \Exception('Unexpected error caused.');
            }
        } else {
            $this->add($key, $value);
        }
    }

    /**
     * Removes a key from the array.
     * @param type $key 
     */
    public function remove($key) {
        if ($this->containsKey($key)) {
            unset($this->keyValuePairs[$key]);
        } else {
            throw new \Exception('The key doesn\'t exists: ' . $key);
        }
    }

    /**
     * Returns the KeyValuePair.
     * @param string $key
     * @return KeyValuePair 
     */
    public function getPair($key) {
        foreach ($this->keyValuePairs as $keyValuePair) {
            if ($keyValuePair->getKey() == $key) {
                return $keyValuePair;
            }
        }
        return null;
    }

    /**
     * Returns the value.
     * @param string $key
     * @return mixed 
     */
    public function get($key) {
        foreach ($this->keyValuePairs as $keyValuePair) {
            if ($keyValuePair->getKey() == $key) {
                return $keyValuePair->getValue();
            }
        }
        return null;
    }

    /**
     * Checks if a key exists,
     * @param string $key
     * @return bool 
     */
    public function containsKey($key) {
        foreach ($this->keyValuePairs as $keyValuePair) {
            if ($keyValuePair->getKey() == $key) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if the value to a key is not empty.
     * @param string $key
     * @return bool 
     */
    public function isNotEmpty($key) {
        foreach ($this->keyValuePairs as $keyValuePair) {
            if ($keyValuePair->getKey() == $key) {
                $value = $keyValuePair->getValue();
                if (!empty($value)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

}

