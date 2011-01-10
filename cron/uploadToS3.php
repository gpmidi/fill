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
//        $dependencies = new XenForo_Dependencies_Public();
//        $dependencies->preLoadData();

 //       XenForo_Session::startPublicSession();
        Log::add('XF initialisation complete!');
// End XenForo

inclib('S3.php');
$s = new S3();
S3::$useSSL = false;

// fetching download list:
$pdo = Database::getHandle();
$a = $pdo->query('SELECT * FROM plugin_downloads_version AS pdv LEFT JOIN plugin_downloads AS pd ON pdv.did = pd.did LEFT JOIN plugins AS p ON p.pid = pd.pid WHERE pdv.isons3 = 0');
//$a = Database::select('plugin_downloads_version', '*', array('isons3 = 0'));
$xfu = new XenForo_Model_User();
echo 'Starting run at '.time().'
';
$filesToDelete = array();
while ($b = $a->fetch()) {
  //print_r($b);
  $u = $xfu->getUserById($b['pauthor_id']);
  $s3nm = "{$u['username']}/{$b['pname']}/{$b['vhash']}/{$b['dfname']}";
  echo "Uploading: {$s3nm}...";
  $mynm = HR_ROOT.'/uploads/'.$b['vhash'];
  $bukketnm = 'filldl.bukkit.org';
  if (!file_exists($mynm)) {
    echo '...file does not exist! Removing from table...';
    if (Database::delete('plugin_downloads_version', array('vid = ?', $b['vid']), 1)) {
      echo '...done!';
    } else {
      echo '...failed. TERMINATING!'; exit();
    }
  } else {
    if( $s->putObject($s->inputFile($mynm), $bukketnm, $s3nm, S3::ACL_PUBLIC_READ) ) {
      echo '...OK!';
      if (Database::update('plugin_downloads_version', array('isons3' => 1), null, array('did = ? and vid = ?', $b['did'], $b['vid'])))
        $filesToDelete[$mynm] = true;
    } else { 
      echo '...:(';
    }
  }
  echo '
';
}
echo '----Beginning upload directory clean up----
';
foreach (array_keys($filesToDelete) as $deletePath) {
  echo "Deleting: {$deletePath}...";
  if (unlink($deletePath)) {
    echo '...:D';
  } else {
    echo '...:(';
  }
  echo PHP_EOL;
}
echo 'Ending run at '.time().'
-------======------


';
