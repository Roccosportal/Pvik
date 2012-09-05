<?php
namespace Pvik\Core;
use Pvik\Core\Config;
use Pvik\Core\Path;
/**
 * A class to handle the log.
 * Source code must be in this file because very basic core functions insists on that.
 */
class Log {

    /**
     * Contains the instance of an Log obejcet.
     * @var Log 
     */
    protected static $Log = null;

    /**
     * Contains an array of the log lines.
     * @var array
     */
    protected $LogTrace;

    /**
     * Contains the handle of the current log file.
     * @var type 
     */
    protected $LogFileHandle = null;

    public function __construct() {
        $this->LogTrace = array();
        $date = new \DateTime();
        if (Config::$Config['Log']['UseOneFile'] == false) {
            $GeneratedFilePath = Path::RealPath('~/logs') . '/log-' . $date->getTimestamp() . '.txt';
        } else {
            $GeneratedFilePath = Path::RealPath('~/logs/log-file.txt');
        }

        $this->LogFileHandle = fopen($GeneratedFilePath, 'w');
    }

    /**
     * Writes a line in the log file and saves it into the log trace.
     * @param string $Message 
     */
    public function Write($Message) {
        If ($this->LogFileHandle != null) {
            fwrite($this->LogFileHandle, $Message);
        }
        array_push($this->LogTrace, $Message);
    }

    /**
     * Writes a line in the log file and saves it into the log trace if logging is turned on.
     * @param string $Message 
     */
    public static function WriteLine($Message) {
        if (Config::$Config['Log']['On'] == true) {
            If (self::$Log == null) {
                self::$Log = new Log();
            }
            self::$Log->Write($Message . "\n");
        }
    }

    /**
     * Returns a log trace.
     * Can be used for a exception page.
     * @return string 
     */
    public static function GetTrace() {
        $TraceString = "";
        If (self::$Log != null) {
            $TraceString = self::$Log->GetTraceString();
        }
        return $TraceString;
    }

    /**
     * Returns a log trace.
     * Can be used for a exception page.
     * @return string 
     */
    public function GetTraceString() {
        $TraceString = "";
        $Max = count($this->LogTrace);
        if ($Max > 0) {
            for ($i = 0; $i < $Max; $i++) {
                if (($i + 1) < 10) {
                    $TraceString .= '#0' . ($i + 1) . ' ' . $this->LogTrace[$i];
                } else {
                    $TraceString .= '#' . ($i + 1) . ' ' . $this->LogTrace[$i];
                }
            }
        }
        return $TraceString;
    }

    /**
     * Destroys the log file handle when finished.
     */
    public function __destruct() {
        if ($this->LogFileHandle != null) {
            fclose($this->LogFileHandle);
        }
    }

}