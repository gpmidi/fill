<?php
//print_r($_POST);
	$params = array_slice($hr_URI, 1);
	$template_settings=array();
	$template_settings['HR_TEMPLATE_TITLE'] = "Upload Complete!";

	$pluginUsername = $params[0];
	$u = new XenForo_Model_User();
	$pluginUserID = $u->getUserIdFromUser($u->getUserByName($pluginUsername));
	$pluginName = $params[1];
	$dbQuery = Database::select('plugins', 'pid', array('pname = ? AND pauthor_id = ?', $pluginName, $pluginUserID));
	if ((User::$role == User::ROLE_GUEST || User::$uid != $pluginUserID) && (User::$role != User::ROLE_ADMIN))
	{
		throw new HttpException(403);
	}
	else if ($dbQuery->rowCount() != 1)
	{
		throw new HttpException(404);
	}
	else
	{
		
	}
