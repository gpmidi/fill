<?php
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
		$pluginID = $dbQuery->fetchColumn(0);
		// Have they uploaded stuff before?
		$dbQuery2 = Database::select('plugin_downloads', '*', array('pid = ?', $pluginID));
		if ($dbQuery2->rowCount() == 0)
		{
			die('ERROR ERROR ERROR');
		}
		$listNess = $dbQuery2->fetchAll();
		$descdata = array();
		print_r($_POST);
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
			if (!isset($dataarray['fname']) || !isset($dataarray['changelog']) || !isset($dataarray['version'])) continue;
			$dbrec = Database::select('plugin_downloads', array('did', 'ddesc'), array('pid = ? AND dfname = ?', $pluginID, $dataarray['fname']));
			$dinfo = $dbrec->fetch(); // download ID and description
			if ($dinfo['ddesc'] == 'notdoneyet') {
				if (isset($dinfo['friendlyname']) && !empty($dinfo['friendlyname'])) { $dfriendname = $dataarray['friendlyname']; }
				else { $dfriendname = $dataarray['fname']; }
				Database::update('plugin_downloads', array('ddesc' => $dataarray['changelog'], 'dfname' => $dataarray['fname'], 'dfriendlyname' => $dfriendname), null, array('did = ?', $dinfo['did']));
				$addLog[] = $dataarray['fname'];
			} else {
				$editLog[] = $dataarray['fname'];
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
		$template_settings['HR_TEMPLATE_CONTENT'] = <<<EOT
				<p>Congratulations! Here's the edit summary:</p>
				$editSummary
EOT
		;
	}
