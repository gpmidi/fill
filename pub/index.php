<?php

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

if (PHP_SAPI == 'cli') { 
	$_SERVER['REQUEST_URI'] = $argv[1];
}
/*

  style rules:

  1. always tabs.
  2. braces on the same line.
  3. camelCase classes, variables and methods
  4. under_scores in function names and file names
  5. filesnames are all lowercase
  6. prefix functions and constants with hr_ (for hRepo) for differntiantion from standard funcs
  7. always use braces on if, else, for, while, foreach, etc.
  8. never use the closing ?> unless needed
  9. all urls end in /

 */

require('../config.php');

require_once( HR_ROOT . '/lib/Twig/Autoloader.php' );

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(HR_ROOT . '/templates/default');

$twig = new Twig_Environment($loader, array(
			'cache' => HR_ROOT . 'templates_cache/',
			'debug' => true,
			'optimizations' => -1
		));


// GAH! Trim URIs first!
$hr_URI = explode('/', urldecode(trim($_SERVER['REQUEST_URI'], '/')));

// URI fixing, to avoid google :( ing
$correctURI = ltrim($_SERVER['REQUEST_URI'], '/');
if (strlen($correctURI) != 0 && substr($correctURI, -1, 1) != '/')
{
	$correctURI = $correctURI . '/';
}
while (strpos($correctURI, '//') !== FALSE)
{
	$correctURI = str_replace('//', '/', $correctURI);
}
if ('/' . $correctURI != $_SERVER['REQUEST_URI'] && count($_POST) == 0 && strlen($_SERVER['REQUEST_URI']) != 0)
{
	header('Location: ' . HR_PUB_ROOT . $correctURI);
	exit();
}
// end URI fix


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
restore_exception_handler();
restore_error_handler();

// End XenForo



inc('db.php');

inc('user.php');

inc('plugin.php');

inc('message.php');

inc('content.php');

inc('http_error.php');

/*
  inc('template.php');
  inc('sidebar.php');
 */



// Mandatory include-everywhere libraries

inclib('phpmailer/class.phpmailer.php'); // because then we can set defaults here

$mailer = new PHPMailer();

$mailer->SetFrom('donotreply@hrepo.com', 'hRepo System');

$mailer->IsSendmail();

User::bootstrap();

Database::insert('page_view_log', array('userid' => User::$uid, 'lpuri' => implode('/', $hr_URI)));
$pViewLogID = Database::getHandle()->lastInsertId();

$out_array = array();
$out_array['HR_MENU_ITEMS'] = array(
	'list' => array(
		'uri' => '/list/',
		'id' => 'topBarLinkList',
		'text' => 'Browse'
	),
	"about" => array(
		"uri" => "/about/",
		"id" => "topBarLinkAbout",
		"text" => "About"
	),
	"faq" => array(
		"uri" => "/faq/",
		"id" => "topBarLinkFAQ",
		"text" => "FAQ"
	),
	"create" => array(
		"uri" => "/create/",
		"id" => "topBarLinkCreateNewPlugin",
		"text" => "Create New Plugin",
		'minrole' => User::ROLE_ADMIN
	),
	"contact" => array(
		"uri" => "/contact/",
		"id" => "topBarLinkContact",
		"text" => "Contact"
	)
);
if (array_key_exists($hr_URI[0], $out_array['HR_MENU_ITEMS']))
{
	$out_array['HR_MENU_ITEMS'][($hr_URI[0])]['class'] = "active";
}
foreach (array_keys($out_array['HR_MENU_ITEMS']) as $keynm)
{
	$a = $out_array['HR_MENU_ITEMS'][$keynm];
	if (!isset($a['minrole']))
		continue;
	if ($a['minrole'] >= User::$role)
		unset($out_array['HR_MENU_ITEMS'][$keynm]);
}
$out_array['HR_TEMPLATE_PUB_ROOT'] = HR_TEMPLATE_PUB_ROOT;

$out_array['THISYEAR'] = date('Y');
// Git commit data START

$commitData = unserialize(file_get_contents('/home2/bukkit/fill/gitcommit.txt'));
$out_array['SHORTCOMMIT'] = $commitData['short'];
$out_array['LONGCOMMIT'] = $commitData['long'];
$out_array['DATECOMMIT'] = ago(strtotime($commitData['commitdate']));
$out_array['USERCOMMIT'] = $commitData['userid'];

function ago($timestamp) {
	$difference = time() - $timestamp;
	$periods = array("second", "minute", "hour", "day", "week", "month", "years", "decade");
	$lengths = array("60", "60", "24", "7", "4.35", "12", "10");
	for ($j = 0; $difference >= $lengths[$j]; $j++)
		$difference /= $lengths[$j];
	$difference = round($difference);
	if ($difference != 1)
		$periods[$j].= "s";
	$text = "$difference $periods[$j] ago";
	return $text;
}

// Git commit data END

try
{
	switch ($hr_URI[0])
	{
		case 'git':
			require_once( HR_ROOT . "pages/git.php" );
			$template = $twig->loadTemplate("index.html");
			$out_array = array_merge($out_array, $template_settings);
			echo $template->render($out_array);
			break;
		case "faq":
			$out_array['HR_TEMPLATE_VARS'] = array('url' => '/faq', 'uri' => 'faq');
			$template = $twig->loadTemplate("faq.html");
			echo $template->render($out_array);
			break;
		case "about":
			$out_array['HR_TEMPLATE_VARS'] = array('url' => '/about', 'uri' => 'about');
			$template = $twig->loadTemplate("about.html");
			echo $template->render($out_array);
			break;
		case "contact":
			$out_array['HR_TEMPLATE_VARS'] = array('url' => '/contact', 'uri' => 'contact');
			$template = $twig->loadTemplate("contact.html");
			echo $template->render($out_array);
			break;
		case "create":
			require_once( HR_ROOT . "pages/plugins/create.php" );
			$template = $twig->loadTemplate("index.html");
			$out_array = array_merge($out_array, $template_settings);
			echo $template->render($out_array);
			break;
		case 'upload':
			require_once( HR_ROOT . "pages/plugins/upload.php" );
			$template = $twig->loadTemplate("upload.html");
			$out_array = array_merge($out_array, $template_settings);
			echo $template->render($out_array);
			break;
		case 'uploadComplete':
			require_once( HR_ROOT . "pages/plugins/uploadComplete.php" );
			$template = $twig->loadTemplate("index.html");
			$out_array = array_merge($out_array, $template_settings);
			echo $template->render($out_array);
			break;
		case 'handleUpload':
			require_once( HR_ROOT . "pages/plugins/handleUpload.php" );
			$template = $twig->loadTemplate("index.html");
			$out_array = array_merge($out_array, $template_settings);
			echo $template->render($out_array);
			break;
		case 'featured_rotator':
			//require_once( HR_ROOT . "pages/plugins/rotator.php" ); // currently disabled for testing
			$template = $twig->loadTemplate("rotator.html");
			echo $template->render($out_array);
			break;
		case 'api':
			require_once( HR_ROOT . 'pages/api.php' );
			exit();
		case 'list':
			require_once( HR_ROOT . 'pages/plugins/list.php' );
			$template = $twig->loadTemplate("list.html");
			$out_array = array_merge($out_array, $template_settings);
			echo $template->render($out_array);
			break;
		case 'detail':
			require_once( HR_ROOT . 'pages/plugins/detail.php' );
			$template = $twig->loadTemplate("detail.html");
			$out_array = array_merge($out_array, $template_settings);
			echo $template->render($out_array);
			break;
		case 'download':
			require_once( HR_ROOT . 'pages/plugins/download.php' );
			break;
		default:
			require_once( HR_ROOT . "pages/index.php" );
			$template = $twig->loadTemplate("index.html");
			$out_array = array_merge($out_array, $template_settings);
			echo $template->render($out_array);
			break;
	}
} catch (HttpException $e)
{ // breakout! dunna dunna dunna
	$template = $twig->loadTemplate($e->getErrorTemplate());
	$template_settings = array(
		'HR_FRIENDLY_ERROR' => $e->getErrorFriendly(),
		'HR_ERROR_CODE' => $e->getErrorCode()
	);
	$out_array = array_merge($out_array, $template_settings);
	echo $template->render($out_array);
} catch (Exception $e) { // breakout! dunna dunna dunna
	$e = new HttpException(500);
	$template = $twig->loadTemplate($e->getErrorTemplate());
	$template_settings = array(
		'HR_FRIENDLY_ERROR' => $e->getErrorFriendly(),
		'HR_ERROR_CODE' => $e->getErrorCode()
	);
	$out_array = array_merge($out_array, $template_settings);
	echo $template->render($out_array);
}
