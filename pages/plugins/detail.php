<?php

	$template_settings=array();
	$params = array_slice($hr_URI, 1);

	$pluginUsername = $params[0];
	$u = new XenForo_Model_User();
	$pluginUserID = $u->getUserIdFromUser($u->getUserByName($pluginUsername));
	$pluginName = $params[1];
	$additional = '';
	if (User::$uid == $pluginUserID || User::$role == User::ROLE_ADMIN) {
		$additional = '-3, -2, -1, ';
	}
	$dbQuery = Database::select('plugins', 'pid', array('pname = ? AND pauthor_id = ? AND pstatus IN ('.$additional.' 0, 1, 2)', $pluginName, $pluginUserID));
	$pluginID = $dbQuery->fetchColumn();
	
	// GOT!
	$thisPlugin = new Plugin($pluginID);
	
	// Markdownify
	inclib('markdown.php');
	
	$descMarkdowned = Markdown($thisPlugin->desc);
	
	$template_settings = array(
		'HR_PLUGIN_ID' => $thisPlugin->getID(),
		'HR_PLUGIN_AUTHOR_ID' => $thisPlugin->author_id,
		'HR_PLUGIN_AUTHOR_NAME' => $pluginUsername,
		'HR_PLUGIN_NAME' => $thisPlugin->name,
		'HR_PLUGIN_DESCRIPTION' => $descMarkdowned,
		'HR_PLUGIN_REQUIREMENTS' => $thisPlugin->reqs,
		//'HR_PLUGIN_NEEDSMYSQL' => $thisPlugin->requires_mysql,
		'HR_PLUGIN_DOWNLOADS' => $thisPlugin->downloads,
		'HR_PLUGIN_ADDED_DATE' => $thisPlugin->added_date,
		'HR_PLUGIN_RATING' => $thisPlugin->rating,
		'HR_PLUGIN_STATUS' => $thisPlugin->status
	);