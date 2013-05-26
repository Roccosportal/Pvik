<?php

namespace Pvik\Web;

use Pvik\Core\Config;
use Pvik\Core\Path;

/**
 * Manages errors and show an error page if a exception is uncaptured
 */
class ErrorManager {

    /**
     * Initialize the error manager
     */
    public static function init() {
        set_error_handler(array('\\Pvik\Web\\ErrorManager', 'CaptureError'));
        set_exception_handler(array('\\Pvik\Web\\ErrorManager', 'CaptureException'));
    }

    /**
     * Captures a php error
     * @param type $errno
     * @param type $errstr
     * @param type $errfile
     * @param type $errline
     * @throws \ErrorException
     */
    public static function captureError($errno, $errstr, $errfile, $errline) {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Captures a non captured exception and shows an error page
     * @param \Exception $exception
     */
    public static function captureException(\Exception $exception) {
        // delete output buffer and ignore it
        ob_get_clean();
        self::showErrorPage($exception);
    }

    /**
     * Tries to show an error page for an exception.
     * @param \Exception $exception 
     */
    public static function showErrorPage(\Exception $exception) {
        try {
            $exceptionClass = get_class($exception);
            $errorPages = Config::$config['ErrorPages'];
            if (isset($errorPages[$exceptionClass])) {
                $file = Path::realPath($errorPages[$exceptionClass]);
                if (file_exists($file)) {
                    self::executeErrorPage($exception, $file);
                } else {
                    throw new \Exception('Erropage ' . $file . ' not found');
                }
            } else {
                $file = Path::realPath($errorPages['Default']);
                if (file_exists($file)) {
                    self::executeErrorPage($exception, $file);
                } else {
                    throw new \Exception('Erropage ' . $file . ' not found');
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Executes the error page file.
     * @param \Exception $exception
     * @param type $file 
     */
    protected static function executeErrorPage(\Exception $exception, $file) {
        require($file);
    }

}

