<?php

require('../config.php');

inc('db.php');

// Load up the XenForo system
        Log::add('Begin initialising XenForo...');
        $startTime = microtime(true);
        //$fileDir = realpath('./../forums/');
        $fileDir = '/home2/bukkit/public_html/forums/';

        require($fileDir . '/library/XenForo/Autoloader.php');
        XenForo_Autoloader::getInstance()->setupAutoloader($fileDir . '/library');

        XenForo_Application::initialize($fileDir . '/library', $fileDir);
        XenForo_Application::set('page_start_time', $startTime);

        // Not required if you are not using any of the preloaded data
        $dependencies = new XenForo_Dependencies_Public();
        $dependencies->preLoadData();

        XenForo_Session::startPublicSession();
        Log::add('XF initialisation complete!');
// End XenForo


// fetching download list:
$a = Database::select('plugin_downloads_version', '*', array('isons3 = 0'));
while ($b = $a->fetch()) {
  echo $a['did'];
}
