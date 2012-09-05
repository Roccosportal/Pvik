<?php
namespace Pvik\Web;
use Pvik\Core\Config;
use Pvik\Core\Path;
class ErrorManager {
    public static function Init(){
        set_error_handler( array( '\\Pvik\Web\\ErrorManager', 'CaptureError' ) );
        set_exception_handler(array( '\\Pvik\Web\\ErrorManager', 'CaptureException'));
    }
    
    public static function CaptureError($errno, $errstr, $errfile, $errline) {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    
    
    public static function CaptureException(\Exception $Exception) {
        // delete output buffer and ignore it
            ob_get_clean();
            self::ShowErrorPage($Exception);
    }
    
    /**
     * Tries to show an error page for an exception.
     * @param Exception $Exception 
     */
    public static function ShowErrorPage(\Exception $Exception) {
        try {
            $ExceptionClass = get_class($Exception);
            $ErrorPages = Config::$Config['ErrorPages'];
            if (isset($ErrorPages[$ExceptionClass])) {
                $File = Path::RealPath($ErrorPages[$ExceptionClass]);
                if (file_exists($File)) {
                    self::ExecuteErrorPage($Exception, $File);
                } else {
                    throw new \Exception('Erropage ' . $File . ' not found');
                }
            } else {
                $File = Path::RealPath($ErrorPages['Default']);
                if (file_exists($File)) {
                    self::ExecuteErrorPage($Exception, $File);
                } else {
                    throw new \Exception('Erropage ' . $File . ' not found');
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Executes the error page file.
     * @param Exception $Exception
     * @param type $File 
     */
    protected static function ExecuteErrorPage(\Exception $Exception, $File) {
        require($File);
    }

    
}
 



    
