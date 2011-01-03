<?php

$nav['uploadComplete'] = array('url' => '/uploadComplete', 'slug' => 'uploadComplete', 'name' => 'Upload Complete', 'loggedInOnly' => 999, 'minRole' => 0, 'weight' => 4, 'extrapre' => '', 'extrapost' => ''); // 1 for only logged in
if ($slug == "uploadComplete")
{
	$pluginUsername = $params[0];
	$u = new XenForo_Model_User();
	$pluginUserID = $u->getUserIdFromUser($u->getUserByName($pluginUsername));
	$pluginName = $params[1];
	$dbQuery = Database::select('plugins', 'pid', array('pname = ? AND pauthor_id = ?', $pluginName, $pluginUserID));
	if ((User::$role == User::ROLE_GUEST || User::$uid != $pluginUserID) && (User::$role != User::ROLE_ADMIN))
	{
		$httpError = 403;
	}
	else if ($dbQuery->rowCount() != 1)
	{
		$httpError = 404;
	}
	else
	{
		$pluginID = $dbQuery->fetchColumn(0);
		// Have they uploaded stuff before?
		$dbQuery2 = Database::select('plugin_downloads', '*', array('pid = ?', $pluginID));
		if ($dbQuery2->rowCount() == 0)
		{
			die('ERROR ERROR ERROR');
		}
		$listNess = $dbQuery2->fetchAll();
		$descdata = array();
		foreach ($_POST as $varname => $varval) {
			if (substr($varname, 0, 9) == 'SWFUpload') {
				$vn = explode('_', $varname);
				$fnum = $vn[2];
				if (!isset($descdata[$fnum])) {
					$descdata[$fnum] = array();
				}
				$descdata[$fnum][$vn[3]] = $varval;
			}
		}
		$editLog = array();
		$addLog = array();
		foreach ($descdata as $dataarray) {
			if (!isset($dataarray['name']) || !isset($dataarray['changelog']) || !isset($dataarray['version'])) continue;
			$dbrec = Database::select('plugin_downloads', array('did', 'ddesc'), array('pid = ? AND dfname = ?', $pluginID, $dataarray['name']));
			$dinfo = $dbrec->fetch(); // download ID and description
			if ($dinfo['ddesc'] == 'notdoneyet') {
				Database::update('plugin_downloads', array('ddesc' => $dataarray['changelog']), null, array('did = ?', $dinfo['did']));
				$addLog[] = $dataarray['name'];
			} else {
				$editLog[] = $dataarray['name'];
			}
			Database::update('plugin_downloads_version', array('vchangelog' => $dataarray['changelog']), null, array('did = ? AND isons3 = 0 AND vchangelog = "notdoneyet"', $dinfo['did']));
		}
		$editSummary = '';
		if (count($addLog) != 0) {
			$editSummary = '<p>You <b>added</b> the following files:</p><ul>';
			foreach ($addLog as $fname) {
				$editSummary .= '<li>'.$fname.'</li>';
			}
			$editSummary .= '</ul>';
		}
		if (count($editLog) != 0) {
                        $editSummary .= '<p>You <b>uploaded new versions of</b> the following files:</p><ul>';
                        foreach ($editLog as $fname) {
                                $editSummary .= '<li>'.$fname.'</li>';
                        }
                        $editSummary .= '</ul>';
                }
		Content::setTitle('Upload complete');
		Content::setContent(<<<EOT
				<p>Congratulations! Here's the edit summary:</p>
				$editSummary
EOT
		);
	}
}
