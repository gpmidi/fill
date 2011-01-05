<?php
	$template_settings=array();
	$template_settings['HR_TEMPLATE_VARS'] = array('url' => '/index', 'uri' => 'index');
	$uname = 'stranger!!';
	$subhead = 'Cake:';
	$itemcontent = '';
	if (isset($_SESSION['message'])) {
		$message = '<p>' . $_SESSION['message'] .'</p>';
		unset($_SESSION['message']);
	}
	if (User::isValid()) {
		$uname = User::$uname;
	}
	
	
	$itemcontent .= '
	<div class="topitem">
		<span class="itemName">World Edit</span><br />
		by <span class="itemAuthor">sk89q</span><br />
		<div class="itemDesc">
			WorldEdit is a powerful tool for Minecraft SMP server that lets you modify
			the world en-masse and undo griefing incidents.  Some of the things that it can do include:
		</div>
		<br />
		<span class="itemDL">Download</span>
	</div>
	';
	
	
	$template_settings['HR_TEMPLATE_CONTENT_HEADER']=$subhead;
	$template_settings['HR_TEMPLATE_CONTENT']=$itemcontent;
	$template_settings['HR_TEMPLATE_JS']=array("system.js");