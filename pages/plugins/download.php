<?php

	$template_settings=array();
	$params = array_slice($hr_URI, 1);

	if (count($params) < 2) throw new HttpException(404);
	$pluginUsername = $params[0];
	$u = XenForo_Model::create('XenForo_Model_User');
	$pluginUserID = $u->getUserIdFromUser($u->getUserByName($pluginUsername));
	$pluginName = $params[1];
	
	$fileID = $versionID = -1;
	if (isset($params[2])) { // this is the FILE id
		$fileID = (int)$params[2];
	}
	if (isset($params[3])) { // this is the VERSION id
		$versionID = (int)$params[3];
	}
	
	$additional = '';
	if (User::$uid == $pluginUserID || User::$role == User::ROLE_ADMIN) {
		$additional = '-3, -2, -1, ';
	}
	$dbQuery = Database::select('plugins', 'pid', array('pname = ? AND pauthor_id = ? AND pstatus IN ('.$additional.' 0, 1, 2)', $pluginName, $pluginUserID));
	$pluginID = $dbQuery->fetchColumn();
	
	try {
		// GOT!
		$thisPlugin = new Plugin($pluginID);
	} catch (NoSuchPluginException $e) {
		throw new HttpException(404);
	}
	$thisPlugin->addDownload();
	$thisPlugin->imprint($pViewLogID);
	
	// Markdownify
	inclib('markdown.php');
	$descMarkdowned = Markdown($thisPlugin->desc);
	
	$pluginDownloads = $thisPlugin->getDownloads();
	//print_r($pluginDownloads);
	
	inc('PluginUpload.php');
	inc('PluginUploadVersion.php');
	if ($fileID != -1) {
		try {
			$thisPluginDownload = new PluginUpload($fileID);
			if ($thisPluginDownload->pluginid != $thisPlugin->getID())
				throw new HttpException(404);
		} catch (NoSuchUploadException $e) {
			throw new HttpException(404);
		}
		
		if ($versionID != -1) {
			try {
				$thisPluginDownloadVersion = new PluginUploadVersion($versionID);
				if ($thisPluginDownloadVersion->downloadid != $thisPluginDownload->getID())
					throw new HttpException(404);
			} catch (NoSuchVersionException $e) {
				throw new HttpException(404);
			}
		} else {
			throw new HttpException(404);
		}
	}
	$thisPluginDownloadVersion->addDownload();
	
	if ($fileID == -1) {
		throw new HttpException(404);
	}
	
	$linkTo = 'http://filldl.bukkit.org/' . $pluginUsername . '/' . $pluginName . '/' . $thisPluginDownloadVersion->hash .  '/' .$thisPluginDownload->filename;
	
	header('Location: ' . $linkTo);