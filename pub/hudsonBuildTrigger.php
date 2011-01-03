<?php

if (!isset($_GET['buildToTrigger'])) {
  exit('No build specified');
}

echo 'Fetching: '.'http://hudson.lukegb.com/job/'.urlencode($_GET['buildToTrigger']).'/build - ...<br /><br />';

echo file_get_contents('http://hudson.lukegb.com/job/'.urlencode($_GET['buildToTrigger']).'/build');
