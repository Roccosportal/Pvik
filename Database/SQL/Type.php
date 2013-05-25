<?php
namespace Pvik\Database\SQL;

class Type {
     /**
     * Converts a value to a sql string.
     * @param mixed $value
     * @return string
     */
    public static function convertValue($value) {
        if (is_bool($value)) {
            if ($value == true)
                return 'TRUE';
            else
                return 'FALSE';
        }
        elseif(is_array($value)){
            $first = true;
            $flatValue = '';
            foreach($value as $singleValue){
                if (!$first) {
                    $flatValue .= ',';
                }
                else {
                    $first = false;
                }
                $flatValue .= $singleValue;
            }
            return $flatValue;
        }
        elseif ($value !== null) {
            return $value;
        } else {
            return 'NULL';
        }
    }
}

