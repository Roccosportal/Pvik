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
    protected $namespaceAssociationList;

    /**
     * 
     */
    public function __construct() {
        $this->namespaceAssociationList = array();
    }

    /**
     * Initialize this class loader
     */
    public function init() {
        spl_autoload_register(array($this, 'LoadClass'));
    }



    /**
     * Tries to load a class.
     * @param String $class  
     */
    protected function loadClass($class) {
        if ($class[0] !== '\\') {
            $class = '\\' . $class;
        }

        $name = $class;
        foreach ($this->getNamespaceAssociationList() as $namespace => $path) {
            if (strpos($name, $namespace . '\\') === 0) { // starts with
                $name = str_replace($namespace, $path, $name);
                break;
            }
        }
	$path = str_replace('\\', '/', $name);
        $path = str_replace('//', '/', $path);
        $path = Path::realPath($path . '.php');
        if (file_exists($path)) {

            require $path;
            if (class_exists('\\Pvik\\Core\\Log')) {
                Log::writeLine('[Include] ' . $path);
            }
            return true;
        }
    }


    /**
     * Returns the current list of namespace associations to paths
     * @return array
     */
    public function getNamespaceAssociationList() {
        return $this->namespaceAssociationList;
    }

    /**
     * Set a path association for a namespace
     * @param String $namespace
     * @param String $path
     * @return \Pvik\Core\ClassLoader
     */
    public function setNamespaceAssociation($namespace, $path) {
        $this->namespaceAssociationList[$namespace] = $path;
        return $this;
    }

}
