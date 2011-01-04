<?php

if (!isset($_GET['buildToTrigger'])) {
  exit('No build specified');
}
// Get reason from $_POST['payload']

$payload = json_decode($_POST['payload']);
$reason = 'Build started due to source change: ' . $payload['before'] . ' -> ' . $payload['after'];

$reason = urlencode($reason);

echo 'Fetching: '.'http://hudson.lukegb.com/job/'.urlencode($_GET['buildToTrigger']).'/build - ...<br /><br />';

file_get_contents('http://hudson.lukegb.com/job/'.urlencode($_GET['buildToTrigger']).'/build?token=BUILDME123&cause='.$reason);
