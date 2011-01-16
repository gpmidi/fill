<?php
//print_r($_POST);
	$params = array_slice($hr_URI, 1);
	$template_settings=array();
	$template_settings['HR_TEMPLATE_TITLE'] = "Upload Complete!";

	$pluginUsername = $params[0];
	$u = XenForo_Model::create('XenForo_Model_User');
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
		if (isset($_POST['bigSubmitButton'])) {
			// input validation:
			$inputForPlugin = array();
			inc('PluginUpload.php');
			inc('PluginUploadVersion.php');
			$pucache = $_SESSION['pluginuploads'];
			foreach ($pucache as $pluginID => $pluginData) {
				if (count($thisPlugin->getDownloads()) == 0) {
					$isNew = true;
				} else {
					$isNew = ($_POST[$pluginID . '_newfile'] == 'new');
				}
				if ($isNew) {
					$thisPluginUpload = new PluginUpload();
					$thisPluginUpload->filename = $_POST[$pluginID . '_filename'];
					$thisPluginUpload->friendlyname = $_POST[$pluginID . '_friendlyname'];
					$thisPluginUpload->description = $_POST[$pluginID . '_desc'];
					$thisPluginUpload->pluginid = $thisPlugin->getID();
					$thisPluginUpload->saveData();
				} else {
					$thisPluginUpload = new PluginUpload($_POST[$pluginID . '_filename']);
				}
				$thisPUVersion = new PluginUploadVersion();
				$thisPUVersion->downloadid = $thisPluginUpload->getID();
				$thisPUVersion->vnumber = $_POST[$pluginID . '_versionnum'];
				$thisPUVersion->changelog = $_POST[$pluginID . '_desc'];
				$thisPUVersion->isprimary = false;
				if ($_POST['primf'] == $pluginID) {
					foreach ($thisPlugin->getDownloads() as $download) {
						Database::update('plugin_downloads_version', array('visprimary' => 0), null, array('did = ?', $download->getID()));
					}
					$thisPUVersion->isprimary = true;
				}
				$thisPUVersion->signature = $pluginData['signature'];
				$thisPUVersion->hash = $pluginData['newfname'];
				$thisPUVersion->saveData();
				unset($_SESSION['pluginuploads'][$pluginID]);
			}
			$form = Message::success('All data saved. Redirecting you to your plugins page in 5 seconds.') . '<script type="text/javascript">setTimeout(function() {window.location = "/detail/'.$pluginUsername.'/'.$pluginName.'/"}, 5000);</script>';
		} else {
		// make the form!
		$form = '<br/><form action="/uploadComplete/'.$params[0].'/'.$params[1].'/" method="POST">';
		if (count($thisPlugin->getDownloads()) == 0) {
			$allowOld = 'display: none;';
			$makePrimary = 'checked="checked"';
			$nonePrimary = '';
			$selectBit = '';
		} else {
			$allowOld = '';
			$makePrimary = '';
			$nonePrimary = '<label for="none_prim">No new primary file:</label><input checked="checked" name="primf" value="none" id="none_primf" type="radio" /><br />';
			$selectBit = '';
			foreach ($thisPlugin->getDownloads() as $pluginUpl) {
				$selectBit .= '<option value="'.$pluginUpl->getID().'">'.$pluginUpl->filename.'</option>';
			}
		}
		$form .= $nonePrimary;
		foreach ($_SESSION['pluginuploads'] as $pluginID => $pluginData) {
			$form .= <<<EOF
			<fieldset>
				<legend>{$pluginData['fname']}</legend>
EOF;
			$form .= <<<EOF
				<label for="${pluginID}_newfile" style="$allowOld">New file?</label>
				<input name="${pluginID}_newfile" id="${pluginID}_newfile" type='checkbox' value='new' onClick='toggleNew("{$pluginID}");' style='$allowOld' checked='checked' /><br />
				<div id="${pluginID}_newFileBit">
					<label for="${pluginID}_filename">Filename:</label>
					<input name="${pluginID}_filename" id="${pluginID}_filename" type='text' value='{$pluginData['fname']}'/><br />
					<label for="${pluginID}_friendlyname">Friendly name (displayed to users on detail page):</label>
					<input name="${pluginID}_friendlyname" id="${pluginID}_friendlyname" type='text' value='{$pluginData['fname']}'/><br />
				</div>
				<div id="${pluginID}_oldFileBit" style="display: none;">
					<label for="${pluginID}_filenamesel">Filename</label>
					<select name="${pluginID}_filename" id="${pluginID}_filenamesel">
						$selectBit
					</select><br />
				</div>
				<label for="${pluginID}_versionnum">Your version number:</label>
				<input name="${pluginID}_versionnum" id="${pluginID}_versionnum" type='text'/><br />
				<label for="${pluginID}_primf">New primary file? (file shown to users on viewing your plugin by default)</label>
				<input $makePrimary name="primf" value="${pluginID}" id="${pluginID}_primf" type='radio' /><br />
				<label for="${pluginID}_desc">Description/Changelog:</label>
				<textarea name="${pluginID}_desc" id="${pluginID}_desc" style="height: 200px;"></textarea><br />
EOF;
			$form .= <<<EOF
			</fieldset>
EOF;
			$makePrimary = '';
		}
		$form .= '<input type="submit" id="bigSubmitButton" name="bigSubmitButton" value="Save Data" /></form>';
		$form .= <<<EOF
<script type="text/javascript">
function toggleNew(fileID) {
	$('#' + fileID + '_newFileBit').toggle('fast');
	$('#' + fileID + '_oldFileBit').toggle('fast');
}
</script>
EOF;
		}
		$template_settings['HR_TEMPLATE_CONTENT_HEADER'] = '<h1>Upload complete</h1>';
		$template_settings['HR_TEMPLATE_CONTENT'] = $form;
	}
