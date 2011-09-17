<?php
self::$Config['IncludeFolders'] = array ('~/Core', '~/Code', '~/Model', '~/Controllers');
self::$Config['Log']['On'] = false;
self::$Config['Log']['UseOneFile'] = true;
self::$Config['UnderConstruction']['Enabled'] = false;
self::$Config['UnderConstruction']['Path'] = '~/Views/Other/UnderConstruction.php';
self::$Config['ErrorPages'] = array (
    'Default' => '~/Views/Errorpages/Master.php'
);
?>
