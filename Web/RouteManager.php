<?php

namespace Pvik\Web;

use Pvik\Core\Config;
use Pvik\Core\Log;
use Pvik\Web\ControllerManager;
use Pvik\Core\Path;

/**
 * Trys to find a controller via the current url
 */
class RouteManager {

    /**
     * Starts the route manager
     * @throws \Pvik\Web\NoRouteFoundException
     */
    public function start() {
        if (Config::$config['UnderConstruction']['Enabled'] == true) {
            $this->executeUnderConstruction(Path::realPath(Config::$config['UnderConstruction']['Path']));
        } else {
            //Request::getInstance()->fetchUrl();
            $request = $this->findRoute();
            if ($request != null) {
                // start output buffering
                ob_start();
                $route = $request->getRoute();
                // execute controller
                ControllerManager::executeController($route['Controller'], $route['Action'], $request);
                // end output buffering and output the buffer
                echo ob_get_clean();
            } else {
                throw new \Pvik\Web\NoRouteFoundException('No route found for ' . $this->url);
            }
        }
    }

    /**
     * Returns the route for the current url.
     * @return Request. 
     */
    protected function findRoute() {
        if (!isset(Config::$config['Routes'])) {
            throw new \Exception('No Routes found in config. Probably misconfigured config.');
        }

        $routes = Config::$config['Routes'];
        $url = $this->fetchUrl();
        foreach ($routes as $route) {
            $request = $this->urlIsMatching($url, $route);
            if ($request != null) {
                return $request;
            }
        }

        return null;
    }

    /**
     * Checks if a url matches with an route. 
     * @param string $orignalUrl
     * @param array $route
     * @return bool
     */
    protected function urlIsMatching($orignalUrl, $route) {
        $routeUrl = $route['Url'];
        if ($routeUrl == '*' || strtolower($orignalUrl) == $routeUrl) {
            $request = new Request();
            $request->setRoute($route);
            $request->setUrl($orignalUrl);

            return $request;
        } elseif (strpos($routeUrl, '{') !== false && strpos($routeUrl, '}') !== false) { // contains a variable
            $routeUrlParts = explode('/', $routeUrl);
            $orignalUrlParts = explode('/', $orignalUrl);
            // have the same part length
            if (count($routeUrlParts) == count($orignalUrlParts)) {
                for ($index = 0; $index < count($routeUrlParts); $index++) {
                    if (strlen($routeUrlParts[$index]) >= 3 && $routeUrlParts[$index][0] == '{') { // it's a variable 
                        $key = substr($routeUrlParts[$index], 1, -1);
                        if (isset($route['Parameters'][$key]) && !preg_match($route['Parameters'][$key], $orignalUrlParts[$index])) {
                            return null;
                        }
                    } else if (strtolower($routeUrlParts[$index]) != $orignalUrlParts[$index]) {
                        // not matching
                        return null;
                    }
                }
                Log::writeLine('Route matching: ' . $route['Url']);
                $request = new Request();
                $request->setRoute($route);
                $request->setUrl($orignalUrl);
                // matching successfull
                // save url parameter
                for ($index = 0; $index < count($routeUrlParts); $index++) {
                    if (strlen($routeUrlParts[$index]) >= 3 && $routeUrlParts[$index][0] == '{') { // it's a variable
                        // the key is the name between the brakets
                        $key = substr($routeUrlParts[$index], 1, -1);
                        // add to url parameters
                        $request->getParameters()->add($key, $orignalUrlParts[$index]);
                        if (isset($route['Parameters'][$key])) {
                            Log::writeLine('Url parameter: ' . $key . ' -> ' . $route['Parameters'][$key] . ' -> ' . $orignalUrlParts[$index]);
                        } else {
                            Log::writeLine('Url parameter: ' . $key . ' -> ' . $orignalUrlParts[$index]);
                        }
                    }
                }
                return $request;
            }
        }
        return null;
    }

    /**
     * Fetches the current url and converts it to a pretty url
     * @return string
     */
    protected function fetchUrl() {
        // get the file base
        $requestUri = $_SERVER['REQUEST_URI'];
        // Delete Parameters
        $queryStringPos = strpos($requestUri, '?');
        If ($queryStringPos !== false) {
            $requestUri = substr($requestUri, 0, $queryStringPos);
        }
        // urldecode for example cyrillic charset
        $requestUri = urldecode($requestUri);
        $url = substr($requestUri, strlen(Path::getRelativeFileBase()));
        if (strlen($url) != 0) {
            // add a / at the start if not already has
            if ($url[0] != '/')
                $url = '/' . $url;

            // add a / at the end if not already has
            if ($url[strlen($url) - 1] != '/')
                $url = $url . '/';
        }
        else {
            $url = '/';
        }

        $this->url = $url;
        return $this->url;
    }

    /**
     * Executes the under construction file.
     * @param type $file 
     */
    protected function executeUnderConstruction($file) {
        require($file);
    }

}