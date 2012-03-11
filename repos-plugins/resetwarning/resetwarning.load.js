
$(document).ready(function() {
	$('input[type="reset"]').click(function() {
		return confirm('Are you sure you want to reset the form to its original contents?');
	});
});
