
$(document).ready(function() {
	$('#submit').click(function() {
		if ($('#submit.loading').length==1) return false;
		$('#submit').addClass('loading');
		return true;
	} );
} );

$(window).unload(function() {
	$('#submit').removeClass('loading');
} );
