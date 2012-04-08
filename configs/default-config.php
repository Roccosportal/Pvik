<?php
self::$Config['IncludeFolders'] = array ('~/core/', '~/code/', '~/model/', '~/controllers/');
self::$Config['Log']['On'] = false;
self::$Config['Log']['UseOneFile'] = true;
self::$Config['UnderConstruction']['Enabled'] = false;
self::$Config['UnderConstruction']['Path'] = '~/views/other/under-construction.php';
self::$Config['ErrorPages'] = array (
    'Default' => '~/views/error-pages/master.php'
);
?>