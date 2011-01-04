<?php

	$template_settings=array();
        $template_settings['HR_TEMPLATE_TITLE'] = "Upload Plugin Files";
        $template_settings['HR_TEMPLATE_JS']=array('jquery.uploadify.min.js','upload.js');
	$template_settings['HR_TEMPLATE_CSS']=array('uploadify.css');
        $template_settings['HR_TEMPLATE_VARS'] = array('url' => '/create', 'uri' => 'create');
	$params = array_slice($hr_URI, 1);
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
		$pluginID = $dbQuery->fetchColumn(0);
		// Have they uploaded stuff before?
		$dbQuery2 = Database::select('plugin_downloads', '*', array('pid = ?', $pluginID));
		$message = '';
		if ($dbQuery2->rowCount() == 0)
		{
			$message = Message::notice('Hi there! It looks like this is the first time you\'ve uploaded files for this plugin. Simply select the files you wish to upload using the file selector below, and then provide details of your uploads in the form which will appear.');
		}
		else
		{
			$listNess = $dbQuery2->fetchAll();
			$list = '';
			foreach ($listNess as $fnameThing)
			{
				$list .= '<li>' . $fnameThing['dfname'] . '</li>';
			}
			$message = Message::notice('The files you currently have on Fill are called:<br /><ul>' . $list . '</ul>');
		}
		$message .= Message::warning('Remember: if you want to upload a new version of a file, that file must be named EXACTLY the same.<br /><br />Example: I previously uploaded AwesomePlugin.jar - to upload a new version, you must ensure that it is named exactly the same.<br /><br /><b>File names cannot be changed once uploaded.</b>');
		$session = array(
			'name' => session_name(),
			'id' => session_id()
		);
		$template_settings['HR_TEMPLATE_CONTENT'] = <<<EOT
				$message
				<div id="uploadBox"></div>
				<form action="/uploadComplete/{$params[0]}/{$params[1]}/" method="POST" id="uploadFormForm">
				<div id="uploadFormArea"></div>
				<input type="submit" id="bigSubmitButton" disabled="disabled" value="Save Data" />
				</form>
				<script type="text/javascript">
	uploadURI = '/handleUpload/{$params[0]}/{$params[1]}/&{$session['name']}={$session['id']}&loadSessionFromGET=true';
				</script>
EOT
		;
	}
