
// Repos 1.4 loads the proplist by default, publishes the event for backwards compatibility
Repos.content('open/', null, function() {
	var container = $('.proplist', this), p, data = {};
	$('dt, dd', container).each(function() {
		if ($(this).is('dt')) {
			p = $(this).text();
		} else {
			data[p] = (data[p] ? data[p] + '\n' : '') + $(this).text();
		}
	});
	// Proplist was once asynghronouse, other plugins bound to this event at DOM ready, so trigger after plugins TODO remove
	$(document).one('repos-content-end', function() {
	$(document).trigger('repos-proplist-loaded', [container, data]);
	for (var p in data) {  
	    if (data.hasOwnProperty(p)) {  
	    	$(container).trigger('repos-proplist-loaded-prop', [p, data[p]]);
	    }
	}
	$(container).trigger('repos-proplist-loaded-prop-all');
	});
} );
