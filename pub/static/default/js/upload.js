jQuery(document).ready(function() {
	jQuery('#uploadBox').uploadify(
	{
		'swf': '/static/images/uploadify/uploadify.swf',
		'uploader': uploadURI,
		'auto': true,
		'multi': true,
		'cancelImage': '/static/images/uploadify/uploadify-cancel.png',
		'removeCompleted': false,
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
		return true;
	});
});
uploadInProgress = false;