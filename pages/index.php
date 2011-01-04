<?php
	$template_settings=array();
	$template_settings['HR_TEMPLATE_VARS'] = array('url' => '/index', 'uri' => 'index');
	$uname = 'stranger!!';
	$message = '';
	if (isset($_SESSION['message'])) {
		$message = '<p>' . $_SESSION['message'] .'</p>';
		unset($_SESSION['message']);
	}
	if (User::isValid()) {
		$uname = User::$uname;
	}
	$template_settings['HR_TEMPLATE_CONTENT']=$message.' You have reached the home of Fill The Bukkit, the global mod repository for <a href="http://bukkit.org">Bukkit</a>, the fabulous mod for <a href="http://minecraft.net">Minecraft</a>, the highly addictive online and single player 8-bit mining game';
