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
		$pluginID = $dbQuery->fetchColumn(0);
		// Have they uploaded stuff before?
		$dbQuery2 = Database::select('plugin_downloads', '*', array('pid = ?', $pluginID));
		if ($dbQuery2->rowCount() == 0)
		{
			die('ERROR ERROR ERROR');
		}
		$listNess = $dbQuery2->fetchAll();
		$descdata = array();
		//print_r($_POST);
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
		$hasNewPrimary = false;
		foreach ($descdata as $dataarray) {
			if (!isset($dataarray['fname']) || !isset($dataarray['changelog']) || !isset($dataarray['version'])) continue;
			$dbrec = Database::select('plugin_downloads', array('did', 'ddesc'), array('pid = ? AND dfname = ?', $pluginID, $dataarray['fname']));
			if ($dataarray['fname'] != $dataarray['origfname']) { // great, now we get to merge them.
				$dbrecnu = Database::select('plugin_downloads', array('did', 'ddesc'), array('pid = ? AND dfname = ?', $pluginID, $dataarray['origfname']));
				// okay, so we've got the database record...
				// does the correct plugin_downloads exit?
				$dlines = $dbrec->fetchAll(); // THIS is what we're changing TO
				$dinfo = $dbrecnu->fetch(); // and this is what we're changing FROM
				if (count($dlines) > 0) { // yes
					$correctDID = $dlines[0]['did'];
					Database::delete('plugin_downloads', array('did = ?', $dinfo['did']));
					// okay, that worked. I hope.
				} else { // no
					$correctDID = $dinfo['did'];
					Database::update('plugin_downloads', array('dfname' => $dataarray['fname'], 'dfriendlyname' => 'notdoneyet'), null, array('did = ?', $correctDID));
				}
				// update plugin_downloads_version to point to correct download
				Database::update('plugin_downloads_version', array('did' => $correctDID), null, array('did = ? AND isons3 = 0 AND vchangelog = "notdoneyet"', $dinfo['did']));
				// and now refetch $dbrec
				$dbrec = Database::select('plugin_downloads', array('did', 'ddesc'), array('did = ?', $correctDID));
			}
			$dinfo = $dbrec->fetch(); // download ID and description
			if ($dinfo['ddesc'] == 'notdoneyet') {
				if (isset($dataarray['friendlyname']) && !empty($dataarray['friendlyname'])) {
					$dfriendname = $dataarray['friendlyname'];
				} else {
					$dfriendname = $dataarray['fname'];
				}
				Database::update('plugin_downloads', array('ddesc' => $dataarray['changelog'], 'dfname' => $dataarray['fname'], 'dfriendlyname' => $dfriendname), null, array('did = ?', $dinfo['did']));
				$addLog[] = $dataarray['fname'];
			} else {
				$editLog[] = $dataarray['fname'];
			}
			$dbS = Database::select('plugin_downloads_version', 'vid', array(
					'did = ? AND isons3 = 0 AND vchangelog = "notdoneyet"',
					$dinfo['did']
				)
				);
			$dbR = $dbS->fetch();
			Database::update('plugin_downloads_version', 
				array(
					'vchangelog' => $dataarray['changelog'],
					'vnumber' => $dataarray['version']
//					'visprimary' => (((bool)$dataarray['newPrimary']) ? '1' : '0')
				),
				null,
				array(
					'vid = ?',
					$dbR['vid']
				)
			);
			if ($dataarray['newPrimary'] == 'yes' || $dataarray['newPrimary'] == '1') {
				$dataarray['newPrimary'] = true;
			} else {
				$dataarray['newPrimary'] = false;
			}
			if ($hasNewPrimary == false) { // on lookout for new primary!
				if ($dataarray['newPrimary']) { // this is it!
					echo 'asdfasdfasdf';
					$hasNewPrimary = $dbR['vid'];
					echo $dbR['vid'];
				}
			}
			print_r($dataarray);
			print_r($hasNewPrimary);
		}
		if ($hasNewPrimary != false) {
			echo 'prim', $hasNewPrimary;//
			$q = Database::getHandle()->prepare('SELECT * FROM `plugins` AS plu LEFT JOIN plugin_downloads AS plud ON plu.pid = plud.pid LEFT JOIN plugin_downloads_version AS pluv ON plud.did = pluv.did WHERE plu.pname = ? AND plu.pauthor_id = ?');
			$q->execute(array($pluginName, $pluginUserID));
			while ($r = $q->fetch()) {
				Database::update('plugin_downloads_version',
					array(
						'visprimary' => ($hasNewPrimary == $r['vid'])
					),
					null,
					array(
						'vid = ?', $r['vid']
					)
				);
			}
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
