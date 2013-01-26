<?php

namespace Pvik\Core;

use Pvik\Core\Path;

/**
 * ClassLoader implements an PSR-0 class loader
 */
class ClassLoader {

    /**
     * List of namespace associations to paths
     * @var array 
     */
    protected $NamespaceAssociationList;

    /**
     * 
     */
    public function __construct() {
        $this->NamespaceAssociationList = array();
    }

    /**
     * Initialize this class loader
     */
    public function Init() {
        spl_autoload_register(array($this, 'LoadClass'));
    }



    /**
     * Tries to load a class.
     * @param String $Class  
     */
    protected function LoadClass($Class) {
        if ($Class[0] !== '\\') {
            $Class = '\\' . $Class;
        }

        $Name = $Class;
        foreach ($this->GetNamespaceAssociationList() as $Namespace => $Path) {
            if (strpos($Name, $Namespace . '\\') === 0) { // starts with
                $Name = str_replace($Namespace, $Path, $Name);
                break;
            }
        }
        $Path = str_replace('//', '/', $Path);
        $Path = Path::RealPath($Path . '.php');
        if (file_exists($Path)) {

            require $Path;
            if (class_exists('\\Pvik\\Core\\Log')) {
                Log::WriteLine('[Include] ' . $Path);
            }
            return true;
        }
    }


    /**
     * Returns the current list of namespace associations to paths
     * @return array
     */
    public function GetNamespaceAssociationList() {
        return $this->NamespaceAssociationList;
    }

    /**
     * Set a path association for a namespace
     * @param String $Namespace
     * @param String $Path
     * @return \Pvik\Core\ClassLoader
     */
    public function SetNamespaceAssociation($Namespace, $Path) {
        $this->NamespaceAssociationList[$Namespace] = $Path;
        return $this;
    }

}
