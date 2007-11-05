
/* not reliable, might abort submit if for some reason event handler is called twice
$(document).ready(function() {
	var s = $('#submit');
	s.click(function() {
		if (s.is('.loading')) return false;
		s.addClass('loading');
		return true;
	} );
} );

$(window).unload(function() {
	$('#submit').removeClass('loading');
} );
*/
