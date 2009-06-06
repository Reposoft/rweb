// automatically adjust input field width for long paths and filenames
$().ready(function() {
	// no pattern matching in services
	if (!/^edit\//.test(Repos.getService())) return;
	// arbitrary safety margin
	var margin = 40;
	var size = {}; // original width
	var left = {}; // original left offset (might be needed when window is made smaller)
	var autosize = function(input, max) {
		var id = input.attr('id');
		if (!id) return;
		if (!size[id]) size[id] = input.width();
		// we have no event for browser text size change, but with a quite big margin we're probably fine anyway
		if (!left[id]) left[id] = input.offset().left; // left offset is not fully reliable when resizing down, so we need to keep the original as reference
		input.width(Math.max(size[id], max - margin - Math.max(left[id],input.offset().left)));
	};
	var run = function() {
		var form = $('form:first');
		var max = $('fieldset',form).width();
		$('input[type="text"], input[type="file"]', form)
				.each( function() {
			autosize($(this), max);
		});
	};
	run();
	$(window).resize(run);
});
