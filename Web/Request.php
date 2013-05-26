<?php

namespace Pvik\Web;

/**
 * Contains data of the current web request.
 */
class Request {

    /**
     * Contains the current url.
     * @var string 
     */
    protected $url;

    /**
     * Contains the parameters from the current url
     * @var \Pvik\Utils\KeyValueArray 
     */
    protected $parameters;

    /**
     * Contains the current route.
     * @var array 
     */
    protected $route;

    /**
     * 
     */
    public function __construct() {
        $this->parameters = new \Pvik\Utils\KeyValueArray();
    }

    /**
     * Returns the current url
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Sets the current url
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * Sets the current route.
     * @param type $route
     */
    public function setRoute(array $route) {
        $this->route = $route;
    }

    /**
     * Returns the current route
     * @return array
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * Returns the current parameters from the url
     * @return \Pvik\Utils\KeyValueArray
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * Returns a $_POST value or null.
     * @param string $key
     * @return string 
     */
    public function getPOST($key) {
        if ($this->isPOST($key)) {
            return $_POST[$key];
        }
        return null;
    }

    /**
     * Checks if a $_POST value is set.
     * @param string $key
     * @return bool 
     */
    public function isPOST($key) {
        return isset($_POST[$key]);
    }

    /**
     * Checks if a $_GET value is set.
     * @param string $key
     * @return bool 
     */
    public function isGET($key) {
        return isset($_GET[$key]);
    }

    /**
     * Returns a $_GET value or null.
     * @param string $key
     * @return string 
     */
    public function getGET($key) {
        if ($this->isGET($key)) {
            return $_GET[$key];
        }
        return null;
    }

    /**
     * Is set to true if a sessions was started.
     * @var bool 
     */
    protected static $sessionStarted = false;

    /**
     * Starts a session if not already started.
     * Use this function to prevent multiple session starts.
     */
    public function sessionStart() {
        if (!self::$sessionStarted) {
            session_start();
            self::$sessionStarted = true;
        }
    }

}
