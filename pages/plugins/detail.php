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
	
	$pdo = Database::getHandle();
	//$sql = 'SELECT pd.*, pdv.* FROM plugin_downloads AS pd JOIN plugin_downloads_version ON pd.did = pdv.did WHERE pd.pid = ?';
	$sql = 'SELECT pd.* FROM plugin_downloads AS pd WHERE pd.pid = ?';
	$getDownloads = $pdo->prepare($sql);
	$getDownloads->execute(array($thisPlugin->getID()));
	$versionsSQL = 'SELECT * FROM plugin_downloads_version WHERE did = ? ORDER BY did DESC LIMIT 1';
	$versionsPre = $pdo->prepare($versionsSQL);
	$lastDownloads = array();
	while ($downloadLine = $getDownloads->fetch()) {
		$thisDownloadArr = array();
		$versionsPre->execute(array($downloadLine['did']));
		if (!($versionsLine = $versionsPre->fetch())) {
			continue; // don't add to downloads array
		}
		// make magical fun time
		if ($versionsLine['isons3'] == 1) { // it is!
			$ending = $pluginUsername . '/' . $pluginName . '/'. $versionsLine['vhash'] . '/' . $downloadLine['dfname'];
			$madeHttpURI = 'http://filldl.bukkit.org/' . $ending;
			$madeHttpsURI = 'https://s3.amazonaws.com/filldl.bukkit.org/' . $ending;
		} else {
			continue; // We COULD do something here, but no.
		}
		$lastDownloads[] = array(
			'name' => $downloadLine['dfriendlyname'],
			'filename' => $downloadLine['dfname'],
			'httpuri' => $madeHttpURI,
			'httpsuri' => $madeHttpsURI,
			'description' => Markdown($downloadLine['ddesc']),
			'lastchangelog' => Markdown($versionsLine['vchangelog']),
			'isfirst' => ($downloadLine['ddesc'] == $versionsLine['vchangelog']),
			'version' => $downloadLine['vnumber']
		);
	}
	
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
		'HR_PLUGIN_DOWNLOADS' => $lastDownloads
	);