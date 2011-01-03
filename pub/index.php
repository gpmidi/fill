<?php

/*
style info:

always tabs.
braces on the same line.
camelCase classes, variables and methods
under_scores in function names and file names
filesnames are all lowercase
prefix functions and constants with hr_ (for hRepo) for differntiantion from standard funcs
always use braces on if, else, for, while, foreach, etc.
never use the closing ?> unless needed

all urls end in /
*/

require('../config.php');

if (isset($_SERVER['PATH_INFO'])) {
	$_GET['page'] = $_SERVER['PATH_INFO']; // if the rewriting is on...
}
if (!isset($_GET['page'])) {
	if (isset($_SERVER['QUERY_STRING'])) {
		$andPos = strpos($_SERVER['QUERY_STRING'], '&');
		$_GET['page'] = substr($_SERVER['QUERY_STRING'], 0, $andPos);
	}
}

// URI fixing, to avoid google :( ing
$correctURI = ltrim($_GET['page'], '/');
if (strlen($correctURI) != 0 && substr($correctURI, -1, 1) != '/') {
	$correctURI = $correctURI . '/';
}
while (strpos($correctURI, '//') !== FALSE) {
	$correctURI = str_replace('//', '/', $correctURI);
}
if ('/' . $correctURI != $_GET['page'] && count($_POST) == 0 && strlen($_GET['page']) != 0) {
	header('Location: '. HR_PUB_ROOT . $correctURI);
	exit();
}
// end URI fix

$_GET['page'] = rtrim($_GET['page'], '/');
$parts = explode('/',$_GET['page']);
if(count($parts) > 1) {
	$slug = $parts[1];
} else {
	$slug = 'index';
}
if (count($parts) > 2) {
	$params = array_slice($parts, 2);
} else {
	$params = array();
}
unset($parts);

// format: $nav['browse'] = array('url' => '/browse', 'slug' => 'browse', 'name' => 'Browse', 'loggedInOnly' => false, 'weight' => 1);
$nav = array();


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

inc('db.php');
inc('content.php');
inc('sidebar.php');
inc('user.php');
inc('template.php');
inc('message.php');
inc('plugin.php');

// Mandatory include-everywhere libraries
inclib('phpmailer/class.phpmailer.php'); // because then we can set defaults here
$mailer = new PHPMailer();
$mailer->SetFrom('donotreply@hrepo.com', 'hRepo System');
$mailer->IsSendmail();

// Now check the user!
User::bootstrap();

foreach(glob(HR_PAGES.'*.php') as $page) {
	require($page);
}

template();
