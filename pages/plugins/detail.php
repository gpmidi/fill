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
			$thisPluginDownloadVersion = $thisPluginDownload->getLatest();
		}
	}
	
	if ($fileID == -1) {
		try {
			$thisPluginDownloadVersion = $thisPlugin->getPrimaryDownloadVersion();
			$thisPluginDownload = new PluginUpload($thisPluginDownloadVersion->downloadid);
		} catch (Exception $e) {
			throw new HttpException(404);
		}
		
	}
	
	foreach ($thisPlugin->getDownloads() as $plugDown) {
		$downloadList[] = array(
			'id' => $plugDown->getID(),
			'friendlyname' => $plugDown->friendlyname,
			'isCurrent' => ($plugDown->getID() == $thisPluginDownload->getID())
		);
	}
	foreach ($thisPluginDownload->getVersions() as $plugVer) {
		$versionList[] = array(
			'id' => $plugVer->getID(),
			'version' => $plugVer->vnumber,
			'isCurrent' => ($plugVer->getID() == $thisPluginDownloadVersion->getID())
		);
	}
	
	$linkTo = 'http://filldl.bukkit.org/' . $pluginUsername . '/' . $pluginName . '/' . $thisPluginDownloadVersion->hash .  '/' .$thisPluginDownload->filename;
	
	$thisDLInfo = array(
		'version' => $thisPluginDownloadVersion->vnumber,
		'changelog' => Markdown($thisPluginDownloadVersion->changelog),
		'description' => Markdown($thisPluginDownload->description),
		'friendlyname' => $thisPluginDownload->friendlyname,
		'showLink' => $thisPluginDownloadVersion->isons3,
		'linkToShow' => $linkTo
	);//
	
	$template_settings = array(
		'HR_PLUGIN_ID' => $thisPlugin->getID(),
		'HR_PLUGIN_AUTHOR_ID' => $thisPlugin->author_id,
		'HR_PLUGIN_AUTHOR_NAME' => $pluginUsername,
		'HR_PLUGIN_NAME' => $thisPlugin->name,
		'HR_PLUGIN_DESCRIPTION' => $descMarkdowned,
		'HR_PLUGIN_REQUIREMENTS' => $thisPlugin->reqs,
		//'HR_PLUGIN_NEEDSMYSQL' => $thisPlugin->requires_mysql,
		'HR_PLUGIN_NO_DOWNLOADS' => $thisPlugin->downloads,
		'HR_PLUGIN_ADDED_DATE' => $thisPlugin->added_date,
		'HR_PLUGIN_RATING' => $thisPlugin->rating,
		'HR_PLUGIN_STATUS' => $thisPlugin->status,
		'HR_PLUGIN_DOWNLOADS' => $downloadList,
		'HR_PLUGIN_VERSIONS' => $versionList,
		'HR_THIS_DOWNLOAD_ID' => $thisPluginDownload->getID(),
		'HR_PLUGIN_THIS_DOWNLOAD' => $thisDLInfo
	);