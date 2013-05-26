<?php

namespace Pvik\Core;

use Pvik\Core\Config;
use Pvik\Core\Path;

/**
 * A class to handle the log.
 */
class Log {

    /**
     * Contains the instance of an Log obejcet.
     * @var Log 
     */
    protected static $instance = null;

    /**
     * Contains an array of the log lines.
     * @var array
     */
    protected $logTrace;

    /**
     * Contains the handle of the current log file.
     * @var type 
     */
    protected $logFileHandle = null;

    /**
     * 
     */
    public function __construct() {
        $this->logTrace = array();
        $date = new \DateTime();
        if (Config::$config['Log']['UseOneFile'] == false) {
            $generatedFilePath = Path::realPath('~/logs') . '/log-' . $date->getTimestamp() . '.txt';
        } else {
            $generatedFilePath = Path::realPath('~/logs/log-file.txt');
        }

        $this->logFileHandle = fopen($generatedFilePath, 'w');
    }

    /**
     * Writes a line in the log file and saves it into the log trace.
     * @param string $message 
     */
    public function write($message) {
        If ($this->logFileHandle != null) {
            fwrite($this->logFileHandle, $message);
        }
        array_push($this->logTrace, $message);
    }

    /**
     * Writes a line in the log file and saves it into the log trace if logging is turned on.
     * @param string $message 
     */
    public static function writeLine($message) {
        if (Config::$config['Log']['On'] == true) {
            If (self::$instance == null) {
                self::$instance = new Log();
            }
            self::$instance->write($message . "\n");
        }
    }

    /**
     * Returns a log trace.
     * Can be used for a exception page.
     * @return string 
     */
    public static function getTrace() {
        $traceString = "";
        If (self::$instance != null) {
            $traceString = self::$instance->getTraceString();
        }
        return $traceString;
    }

    /**
     * Returns a log trace.
     * Can be used for a exception page.
     * @return string 
     */
    public function getTraceString() {
        $traceString = "";
        $max = count($this->logTrace);
        if ($max > 0) {
            for ($i = 0; $i < $max; $i++) {
                if (($i + 1) < 10) {
                    $traceString .= '#0' . ($i + 1) . ' ' . $this->logTrace[$i];
                } else {
                    $traceString .= '#' . ($i + 1) . ' ' . $this->logTrace[$i];
                }
            }
        }
        return $traceString;
    }

    /**
     * Destroys the log file handle when finished.
     */
    public function __destruct() {
        if ($this->logFileHandle != null) {
            fclose($this->logFileHandle);
        }
    }

}