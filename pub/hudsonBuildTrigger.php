<?php

if (!isset($_GET['buildToTrigger'])) {
  exit('No build specified');
}

exit(file_get_contents('http://hudson.lukegb.com/job/'.urlencode($_GET['buildToTrigger']).'/build'));
