<?php

if($slug == "phpinfol33tHAXX") {

	ob_start();
	phpinfo();
	$phpinfo = ob_get_contents();
	ob_end_clean();
	Content::setContent('LOL PHPINFO');
	Content::setContent(<<<EOT
	<p>$phpinfo</p>
	


EOT
	);
	
}
