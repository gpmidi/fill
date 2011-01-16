<?php
	$template_settings=array();
	$template_settings['HR_TEMPLATE_TITLE'] = "Create New Plugin";
	$template_settings['HR_TEMPLATE_JS']=array("plugincreateform.js");
	$template_settings['HR_TEMPLATE_VARS'] = array('url' => '/create', 'uri' => 'create');
	/* if (User::$role < User::ROLE_ADMIN)
	{
		$template_settings['HR_TEMPLATE_CONTENT'] = "This site isn't ready yet! Please wait until everything is ready!";
	}
	else */ if (User::$role == User::ROLE_GUEST)
	{
		throw new HttpException(403);
	}
	else
	{
		$message = $pname = $pdesc = $preqs = $pmysql = $ismyplugin = $pauthorname = $pauthornameVis = '';

		if (User::$role < User::ROLE_DEVELOPER)
		{
			$message .= Message::notice('As you are not registered as a developer, you won\'t be able to control any plugins you upload here, and they will not be marked as yours.<br />Once you have been moved to the Developers group, you will gain access to edit your plugin.'); // default message
			$ismyplugin = 'disabled="disabled';
		}

		if (isset($_POST['submit']) && $_POST['submit'] == 'Create!')
		{
			if (User::$role < User::ROLE_DEVELOPER)
			{
				$_POST['ismyplugin'] = ''; // force no :D
			}

			// stuff happens here.
			$newPlugin = new Plugin();
			$pname = $newPlugin->name = htmlentities($_POST['pname']);
			$pdesc = $newPlugin->desc = htmlentities($_POST['pdesc']);
			$preqs = $newPlugin->reqs = htmlentities($_POST['preqs']);
			$newPlugin->categories = $_POST['pcategory'];
			//print_r($_POST['pcategory']); exit();
			//$pmysql .= ( $_POST['pmysql'] == 'yes') ? ' checked="checked"' : '';
			$ismyplugin .= ( $_POST['ismyplugin'] == 'yes') ? ' checked="checked"' : '';
			$newPlugin->requires_mysql = 0;
			$newPlugin->author_id = User::$uid;
			$pauthorname = $newPlugin->real_author_name = ($_POST['ismyplugin'] == 'yes') ? '' : htmlentities($_POST['pauthorname']);
			$pauthornameVis = ($_POST['ismyplugin'] == 'yes') ? ' style="display:none;"' : '';
			$newPlugin->status = ($_POST['ismyplugin'] == 'yes') ? -1 : -2;
			if ($newPlugin->saveData())
			{
				redirect('/upload/' . User::$uname . '/' . $newPlugin->name . '/');
			}
			else
			{
				$message .= Message::error('An error occurred whilst adding the plugin to the database. Please contact an hRepo administrator.');
			}
		}
	
	// fetch categories:
	$catQ = Database::select('categories', array('cid','cname'));
	$pcategory = '';
	while ($catR = $catQ->fetch(PDO::FETCH_ASSOC)) {
		$pcategory .= '<div style="border-bottom: 1px solid #e0e0e0; clear: both; width: 100%;"><label style="display: inline; font-size: small; float: left;" for="'.$catR['cid'].'_chkbx">'.$catR['cname'].'</label><input type="checkbox" id="'.$catR['cid'].'_chkbx" value="'.$catR['cid'].'" name="pcategory[]" style="float: right;" /></div>';
	}
	$pcategory .= '<div style="border-top: 1px solid #e0e0e0; clear: both; width: 100%;">&nbsp;</div>';
	
	
	$template_settings['HR_TEMPLATE_CONTENT'] = "

	<h3>Step 1 of 2</h3>
			".$message."
		<form action=\"/create/\" method=\"POST\">
			<div class=\"form-row\">
				<label for=\"pname\">Plugin Name</label>
				<span><input type=\"text\" name=\"pname\" id=\"pname\" value=\"".$pname."\" /></span>
			</div>
			<div class=\"form-row\">
				<!--<label for=\"pcategory\">Plugin Category</label>-->
				<fieldset style=\"width: 50%;\">
				<legend>Plugin Category</legend>
				<span id=\"pcategory\">".$pcategory."</span>
				</fieldset>
			</div>
			<div class=\"form-row\">
				<label for=\"pdesc\">Description (<a href=\"/markdown/\">Markdown</a> formatted)</label>
				<span><textarea name=\"pdesc\" id=\"pdesc\">".$pdesc."</textarea></span>
			</div>
			<div class=\"form-row\">
				<label for=\"preqs\">Requirements</label>
				<span><input type=\"text\" name=\"preqs\" id=\"preqs\" value=\"".$preqs."\" /></span>
			</div>
			<div class=\"form-row\">
				<label for=\"ismyplugin\">Is My Own Plugin?</label>
				<span><input type=\"checkbox\" name=\"ismyplugin\" id=\"ismyplugin\" value=\"yes\" ".$ismyplugin." /></span>
			</div>
			<div class=\"form-row\" id=\"pauthornameRow\" $pauthornameVis>
				<label for=\"pauthorname\">Real Author Name (if not yours)</label>
				<span><input type=\"text\" name=\"pauthorname\" id=\"pauthorname\" value=\"".$pauthorname."\" /></span>
			</div>
			<div class=\"form-row form-row-last\">
				<span><input type=\"submit\" name=\"submit\" id=\"submitBtn\" value=\"Create!\" /></span>
			</div>
		</form>
		<script type='text/javascript'>
			$(document).ready(function() {
				$('#pauthorname').click(function() {
					$('#pauthornameRow').toggle('fast');
				});
			});
		";
	}
