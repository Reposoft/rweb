// automatically adjust input field width for long paths and filenames
$().ready(function() {
	// no pattern matching in services
	if (!/^edit\//.test(Repos.getService())) return;
	// arbitrary safety margin
	var margin = 40;
	var autosize = function(input) {
		var max = input.parents().filter('fieldset').width();
		input.width(Math.max(input.width(), max - input.offset().left - margin));
	};
	$('input[type="text"], input[type="file"]').each( function() {
		autosize($(this));
	});
});
