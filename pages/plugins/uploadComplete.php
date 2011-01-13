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
		$thisPlugin = new Plugin($dbQuery->fetchColumn());
		//print_r($_SESSION);
		if (!isset($_SESSION['pluginuploads']) || count($_SESSION['pluginuploads']) == 0) {
			throw new HttpException(403); // didn't upload anything
		}
		print_r($_SESSION['pluginuploads']);
		// make the form!
		$form = '<br/><form action="/uploadComplete/'.$params[0].'/'.$params[1].'/" method="POST">';
		foreach ($_SESSION['pluginuploads'] as $pluginID => $pluginData) {
			$form .= <<<EOF
			<fieldset>
				<legend>{$pluginData['fname']}</legend>
EOF;
			$form .= <<<EOF
				<label for="${pluginID}_newfile">New file?</label><input name="${pluginID}_newfile" id="${pluginID}_newfile" type='checkbox' value='new' /><br />
EOF;
			$form .= <<<EOF
			</fieldset>
EOF;
		}
		$form .= '<input type="submit" id="bigSubmitButton" disabled="disabled" value="Save Data" /></form>';
		$template_settings['HR_TEMPLATE_CONTENT_HEADER'] = '<h1>Upload complete</h1>';
		$template_settings['HR_TEMPLATE_CONTENT'] = $form;
	}
