
var submitted = false;
$(document).ready(function() {
	$('#submit').removeClass('.loading').click(function() {
		if (submitted) {
			console.log('not again');
			return false;
		}
		submitted = true;
		$(this).addClass('.loading');
		return true;
	} );
} );
