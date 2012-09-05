<?php

self::$Config['DefaultNamespace'] = '\\MyProject';
self::$Config['NamespaceAssociations']['\\MyProject'] = '~/application/';

self::$Config['Routes'] = array(
    array ('Url' => '/', 'Controller' => 'HelloWorld', 'Action' => 'Index'),
);
?>