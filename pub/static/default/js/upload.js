jQuery(document).ready(function() {
	jQuery('#uploadBox').uploadify(
	{
		'swf': '/static/images/uploadify/uploadify.swf',
		'uploader': uploadURI,
		'auto': true,
		'multi': true,
		'cancelImage': '/static/images/uploadify/uploadify-cancel.png',
		'onSelect': function(file) {
			setTimeout(jQuery.proxy(setTimeStuff, file), 2);
		},
		'onUploadStart': function(file) {
			jQuery('#' + file.id + '_currentStatus').html("<div class='message message-info'><p>Upload beginning...</p></div>");
			uploadInProgress = true;
		},
		'onUploadSuccess': function(file, data, response) {
			jQuery('#' + file.id + '_currentStatus').html("<div class='message message-success'><p>Upload complete!</p></div>");
			itemsSubmitted = itemsSubmitted + 1;
		},
		'onUploadError': function(file, errCode, errMsg) {
			jQuery('#' + file.id + '_currentStatus').html("<div class='message message-error'><p>Upload failed...</p></div>");
			if (errCode == SWFUpload.UPLOAD_ERROR.FILE_CANCELLED) {
				jQuery('#' + file.id + '_details').remove();
			}
		},
		'onUploadComplete': function(file, queue) {
			uploadInProgress = (queue.queueLength > 0);
			if (!uploadInProgress) {
				$('#bigSubmitButton').removeAttr('disabled');
			} else {
				$('#bigSubmitButton').attr('disabled', 'disabled');
			}
		}
	});
	jQuery('#uploadFormForm').submit(function() {
		if (uploadInProgress == true) {
			alert('An upload is currently in progress. Please wait until it has completed before submitting the form.');
			return false;
		}
		if (itemsSubmitted == 0) {
			alert('You must upload something before you can submit this form.');
			return false;
		}
		return true;
	});
});
uploadInProgress = false;
itemsSubmitted = 0;

function buildSelector() {
	showRow = "<br /><select name='" + fileId + "_fname'>";
//	eval('window.nameCur = window.name_' + fileId + ';');
	nameCur = this.name;
	fileId = this.id;
	for (x in window.listOfPreFiles) {
		x = window.listOfPreFiles[x];
		selected = '';
		if (x == nameCur) {
			selected = ' selected="selected"';
		}
		showRow = showRow + "<option value='"+x+"'"+selected+">" + x + "</option>";
	}
	showRow = showRow + "</select>";
	return showRow;
}

function buildTextRow() {
	file = this;
	fileId = file.id;
	fileName = file.name;
	rowOut = "<br /><label for='" + fileId + "_fname'>File name (used on download e.g. ShadowPlugin.jar, required):</label><input type='text' name='" + fileId + "_fname' value='"+fileName+"' /><br />";
	rowOut = rowOut + "<label for='" + fileId + "_friendlyname'>File friendly name (used on plugin detail page e.g. Main Plugin, optional, defaults to filename):</label><input type='text' name='" + fileId + "_friendlyname' value='"+fileName+"' /><br />";
	return rowOut;
}

function toggleShowRow() {
	showRow = jQuery('#' + this.id + '_showRow');
	if (showRow.hasClass('newFile')) { // so it's an old file now:
		showRow.removeClass('newFile');
		showRow.addClass('oldFile');
		showRow.html(jQuery.proxy(buildSelector,this)());
	} else { // now it's a new file
		showRow.removeClass('oldFile');
		showRow.addClass('newFile');
		showRow.html(jQuery.proxy(buildSelector,this)());
	}
	//jQuery('#' + this.id + '_showRow').toggle();
}

function setTimeStuff() {
			file = this;
			makeTheRow = "<div id='"+file.id+"_details'><fieldset><legend>"+file.name+"</legend>";
			makeTheRow = makeTheRow + "<div id='"+file.id+"_currentStatus'></div>";
			//makeTheRow = makeTheRow + "<br /><div id='"+file.id+"_newUploadForm'><label for='"+file.id+"_newname'>Friendly name:</label><input type='text' name='"+file.id+"_newname' id='"+file.id+"_newname' value='"+file.name+"' /></div>";
			newFileChecked = " checked='checked'";
			showRow = jQuery.proxy(buildTextRow, file)();
			whatIs = 'newFile';
			
			
			if (jQuery.inArray(file.name, window.listOfPreFiles)) {
				newFileChecked = "";
				whatIs = 'oldFile';
				//showRow = "<input type='hidden' name='"+file.id+"_name' value='"+file.name+"' />";
				showRow = jQuery.proxy(buildSelector, file)();
			}
			
			makeTheRow = makeTheRow + "<br /><label for='"+file.id+"_isNewFile'>New file:</label><input type='checkbox' id='"+file.id+"_showRowCheck' "+newFileChecked+" />";
			makeTheRow = makeTheRow + "<div id='"+file.id+"_showRow' class='"+whatIs+"'>"+showRow+"</div>";
			makeTheRow = makeTheRow + "<br /><label for='"+file.id+"_version'>File version (required):</label><input type='text' name='"+file.id+"_version' id='"+file.id+"_version' /><br />";
			makeTheRow = makeTheRow + "<label for='"+file.id+"_changelog'>File Description (required):</label><textarea name='"+file.id+"_changelog' id='"+file.id+"_changelog' style='width: 100%; height: 250px;'></textarea></fieldset></div>";
			jQuery('#uploadFormArea').append(
				makeTheRow
				);
			jQuery('#' + file.id + '_showRowCheck').click(jQuery.proxy(toggleShowRow, file));
		}