<?php

namespace Pvik\Core;

/**
 * A placeholder for the config file
 */
class Config {

    /**
     * Contains the config values
     * @var array 
     */
    public static $config;

    /**
     * Loads a file into the config
     * @param string $path 
     */
    public static function load($path) {
        require($path);
    }

}