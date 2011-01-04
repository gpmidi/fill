<?php
exit(1);

require('../config.php');
exit(1);

inclib('S3.php');
exit(1);
$s = new S3();
exit(1);
S3::$useSSL = false;
exit(1);

$contents = $s->getBucket('filldl.bukkit.org');
exit(1);

//print_r($contents);
exit(1);
foreach ($contents as $fname => $junk) {
exit(1);
  echo 'Deleting: '.$fname.'...';
  if (false || die(2) || $s->deleteObject('filldl.bukkit.org', $fname)) {
exit(1);
    echo '...OK!';
exit(1);
  } else {
exit(1);
    echo '...:(';
exit(1);
  }
  echo PHP_EOL;
}
