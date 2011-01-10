<?php

	$template_settings=array();
	$params = array_slice($hr_URI, 1);

	$pluginUsername = $params[0];
	$u = new XenForo_Model_User();
	$pluginUserID = $u->getUserIdFromUser($u->getUserByName($pluginUsername));
	$pluginName = $params[1];
	$dbQuery = Database::select('plugins', 'pid', array('pname = ? AND pauthor_id = ?', $pluginName, $pluginUserID));
	$pluginID = $dbQuery->fetchColumn();
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
		// get the plugin object
		$pluginObj = new Plugin($pluginID);
		$isTrusted = ($pluginObj->status == Plugin::STATE_TRUSTED || $pluginObj->status == Plugin::STATE_HIDDEN_TRUSTED) ? true : false;
		// okay, let's do this
		// get down on it
		$tempFile = $_FILES['Filedata']['tmp_name'];
		$fileMd5 = md5_file($tempFile);
		$newFileName = $fileMd5;
		$fileDir = HR_ROOT . '/uploads/';
		$signedHashDir = HR_ROOT . '/signedHashes/';
		if (!file_exists($fileDir))
		{
			mkdir($fileDir, true);
		}
		if (!file_exists($signedHashDir))
		{
			mkdir($signedHashDir, true);
		}
		if (file_exists($fileDir . $newFileName))
		{
			echo 'File exists';
			exit();
		}
		$a = Database::select('plugin_downloads', '*', array('dfname = ?', $_FILES['Filedata']['name']));
		$lastNum = 0;
		if ($a->rowCount() == 0) // if download doesn't already exist...
		{ // create download
			Database::insert('plugin_downloads', array('pid' => $pluginID, 'dfname' => $_FILES['Filedata']['name'], 'dfriendlyname' => 'notdoneyet', 'ddesc' => 'notdoneyet'));
			$a = Database::select('plugin_downloads', '*', array('dfname = ?', $_FILES['Filedata']['name']));
			$pluginFileRow = $a->fetch(PDO::FETCH_ASSOC);
		}
		else
		{ // fetch current download id
			$pluginFileRow = $a->fetch(PDO::FETCH_ASSOC);
			$b = Database::select('plugin_downloads_version', 'vnumber', array('did = ?', $pluginFileRow['did']));
			// set new internal version number
			$lastNum = $b->fetchColumn();
		}
		Database::insert('plugin_downloads_version', array('did' => $pluginFileRow['did'], 'vnumber' => $lastNum + 1, 'vhash' => $fileMd5, 'vdate' => date('Y-m-d H:i:s'), 'vchangelog' => 'notdoneyet', 'isons3' => '0', 'vsignature' => 'signing in progress'));
		$vID = Database::getHandle()->lastInsertID();
		
		//file_put_contents($signedHashDir . $vid . '.sha256', sha256_file($tempFile));
		$fileToGetFrom = '/home2/bukkit/fillbukkit.'.((!$isTrusted)?'un':'').'trusted.pem';
		$publicKeyFile = '/home2/bukkit/fillbukkit.'.((!$isTrusted)?'un':'').'trusted.pub';
		$privateKey = openssl_get_privatekey(file_get_contents($fileToGetFrom));
		$publicKey = openssl_get_publickey(file_get_contents($publicKeyFile)); // just in case
		$signature = '';
		openssl_sign(file_get_contents($tempFile) . '--' . $pluginUsername . '//' . $pluginName . '//' . $vID, $signature, $privateKey, 'sha256');
		//$signature = pack('H*', $signature);
		$signature = bin2hex($signature);
		file_put_contents($signedHashDir . $vID . '.sig', $signature);
		
		Database::update('plugin_downloads_version', array('vsignature' => $signature), null, array('vid = ?', $vID));
		
		
		$_SESSION['plugin_' . $_FILES['Filedata']['name']] = $vID;
		move_uploaded_file($tempFile, $fileDir . $newFileName);
		echo '1';
		exit();
	}
