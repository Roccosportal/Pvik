<?php
namespace Pvik\Core;
class Path {
      /**
     * Contains the absoulte file base.
     * Example: /var/www/sub-folder/
     * @var type
     */
    protected static $AbsoluteFileBase;
    
    protected static $RelativeFileBase;
    
    public static function Init(){
        self::$AbsoluteFileBase = getcwd() . '/';
        self::$RelativeFileBase = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
    }
    
    public static function GetAbsoluteFileBase(){
        return self::$AbsoluteFileBase;
    }
    
        public static function GetRelativeFileBase(){
        return self::$RelativeFileBase;
    }
    
      /**
     * Returns an absolute path.
     * Resolves the ~/ symbol.
     * Example /var/www/sub-folder/something.php
     * @param string $Path
     * @return string 
     */
    public static function RealPath($Path) {
        $NewFilePath = str_replace('~/', self::$AbsoluteFileBase, $Path);
        return $NewFilePath;
    }

    /**
     * Returns a relative path.
     * Resolves the ~/ symbol.
     * Example /sub-folder/something.js
     * @param strubg $Path
     * @return type
     */
    public static function RelativePath($Path) {
        $NewPath = str_replace('~/', self::$RelativeFileBase, $Path);
        return $NewPath;
    }

        /**
     * Converts a name to a safe path name. Converts ThisIsAnExample to this-is-an-example.
     * @param string $Name
     * @return string 
     */
    public static function ConvertNameToPath($Name) {
        $ProcessingName = preg_replace("/([a-z])([A-Z][A-Za-z0-9])/", '${1}-${2}', $Name);
        $ProcessingName = str_replace('\\', '/', $ProcessingName);
        return strtolower($ProcessingName);
    }
    
}
?>
