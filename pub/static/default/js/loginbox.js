boxIsUp = true;

function toggleArrowHead() {
	boxIsUp = !boxIsUp;
	if (boxIsUp) {
		setTo = '&#2303;';
		$('#loginDropDown').slideUp();
	} else {
		setTo = '&#2302;';
		$('#loginDropDown').slideDown();
	}
	$('#loginArrowHead').html(setTo);
}

$(function(){
	toModify = '#topBarLinkLogin a';
	$(toModify).append(' <span id="loginArrowHead">&#x2303;</span>');
	$(toModify).click(function() {
		toggleArrowHead();
		return false;
	});
});
