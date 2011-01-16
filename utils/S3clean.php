<?php

if (PHP_SAPI != 'cli') exit('Must be run from command line'. PHP_EOL);

$allowRun = false;
if (isset($argv[1]) && $argv[1] == '--yes' && isset($argv[2]) && $argv[2] == '--aperture-clear') {
  echo 'Firing laser...';
  $allowRun = true;
}
else {
  echo 'This script CANNOT continue.', PHP_EOL;
}

define('ALLOW_RUN', $allowRun);
require('../config.php');
if (!ALLOW_RUN) exit(1);

inclib('S3.php');
inc('db.php');
$s = new S3();
S3::$useSSL = false;

$contents = $s->getBucket('filldl.bukkit.org');
if (!ALLOW_RUN) exit(1);

echo 'Cleaning database...', PHP_EOL;
echo 'Truncating plugin_downloads_version...';
if (Database::getHandle()->exec('TRUNCATE plugin_downloads_version') != 0) {
  echo '...:)', PHP_EOL;
} else {
  echo '...! Failed.', PHP_EOL;
}
echo 'Truncating plugin_downloads...';
if (Database::getHandle()->exec('TRUNCATE plugin_downloads') != 0) {
  echo '...:)', PHP_EOL;
} else {
  echo '...! Failed.', PHP_EOL;
}
echo 'Truncating plugins...';
if (Database::getHandle()->exec('TRUNCATE plugins') != 0) {
  echo '...:)', PHP_EOL;
} else {
  echo '...! Failed.', PHP_EOL;
}
echo 'Truncating plugin_cat_pivot...';
if (Database::getHandle()->exec('TRUNCATE plugin_cat_pivot') != 0) {
  echo '...:)', PHP_EOL;
} else {
  echo '...! Failed.', PHP_EOL;
}
echo 'Database cleaning complete. Clearing filesystem...', PHP_EOL;
foreach (scandir(HR_ROOT . '/uploads/') as $fname) {
  if ($fname == '.' || $fname == '..') continue;
  echo $fname, ': ';
  if (!preg_match('/[0-9a-f]{32}/', $fname)) {
    echo 'Filename does NOT match pattern for MD5. Skipping!', PHP_EOL;
    continue;
  }
  if (!md5_file(HR_ROOT . '/uploads/'. $fname) == $fname) {
    echo 'Filename hash does NOT match MD5 hash of file. Skipping!', PHP_EOL;
    continue;
  }
  if (unlink(HR_ROOT . '/uploads/'. $fname)) {
    echo 'DELETED.', PHP_EOL;
  } else {
    echo 'Delete failed.', PHP_EOL;
    exit(3);
  }
}
echo 'Filesystem cleaning complete. Clearing S3...', PHP_EOL;
//print_r($contents);
foreach ($contents as $fname => $junk) {
  if (!ALLOW_RUN) exit(1);
  echo 'Deleting: '.$fname.'...';
  if ($s->deleteObject('filldl.bukkit.org', $fname)) {
    echo '...OK!';
  } else {
    echo '...:(';
  }
  echo PHP_EOL;
}
