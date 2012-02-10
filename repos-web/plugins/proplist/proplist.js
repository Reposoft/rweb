
// Repos 1.4 loads the proplist by default, publishes the event for backwards compatibility
Repos.service('details/', function() {
	
	
	$(document).trigger('repos-proplist-loaded', [$('div.proplist')]);
} );
