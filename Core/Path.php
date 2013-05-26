<?php

namespace Pvik\Core;

/**
 * Static class with usefull functions for handling paths.
 */
class Path {

    /**
     * Contains the absolute file base (server path)
     * Example: /var/www/sub-folder/
     * @var string
     */
    protected static $absoluteFileBase;

    /**
     * Contains the relative file path (http path)
     * @var string 
     */
    protected static $relativeFileBase;

    /**
     * Initalizes the absolute and relative file path. 
     */
    public static function init() {
        self::$absoluteFileBase = getcwd() . '/';
        self::$relativeFileBase = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
    }

    /**
     * Returns the absolute file base (server path)
     * @return string
     */
    public static function getAbsoluteFileBase() {
        return self::$absoluteFileBase;
    }

    /**
     * Returns the relative file base (http path)
     * @return string
     */
    public static function getRelativeFileBase() {
        return self::$relativeFileBase;
    }

    /**
     * Returns an absolute path (server path).
     * Resolves the ~/ symbol.
     * Example /var/www/sub-folder/something.php
     * @param string $path
     * @return string 
     */
    public static function realPath($path) {
        $newFilePath = str_replace('~/', self::$absoluteFileBase, $path);
        return $newFilePath;
    }

    /**
     * Returns a relative path (http path).
     * Resolves the ~/ symbol.
     * Example /sub-folder/something.js
     * @param string $path
     * @return string
     */
    public static function relativePath($path) {
        $path = str_replace('~/', self::$relativeFileBase, $path);
        return $path;
    }

    /**
     * Converts a name to a safe path name. Converts ThisIsAnExample to this-is-an-example.
     * @param string $name
     * @return string 
     */
    public static function convertNameToPath($name) {
        $name = preg_replace("/([a-z])([A-Z][A-Za-z0-9])/", '${1}-${2}', $name);
        $name = str_replace('\\', '/', $name);
        return strtolower($name);
    }

}
