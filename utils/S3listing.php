<?php

require('../config.php');

inclib('S3.php');
$s = new S3();
S3::$useSSL = false;

$contents = $s->getBucket('filldl.bukkit.org');

//print_r($contents);
echo (implode(PHP_EOL, array_keys($contents)));
