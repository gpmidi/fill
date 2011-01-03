<?php

if (empty(Content::$content) && !isset($httpError)) {
	$httpError = 404;
}
if (!isset($httpError)) {
	$httpError = 200;
}

 if ($httpError == 403) {
	Content::$forcedTitle = "403 Unauthorised";
	Content::$status = "403 Unauthorised";
	$maybeLogin = '';
	if (User::$role == User::ROLE_GUEST) {
		$maybeLogin = '<p>You might need to <a href="/login/">log in</a> to access this area.</p>';
	}
	Content::setTitle('Access Denied');
	Content::setContent(<<<EOT
	<p>You do not have the correct privileges to access this area.</p>
	$maybeLogin
EOT
	);
} else if ($httpError == 404) {
	Content::$forcedTitle = "404 Error";
	Content::$status = "404 Not Found";
	Content::setTitle('404 - Giga Puddi Not Locatable');
	Content::setcontent(<<<EOT
	<p>You suck.</p>
EOT
	);
}
