/**
 * Open details page by default when clicking a file,
 * but preserve the real URL for right clicking.
 */
/* Disabled now that index URLs append ?rweb=details by default
Repos.service('index/', function() {
	$('a.file').click(function(ev) {
		ev.stopPropagation();
		var filename = $(this).text();
		var path = Repos.getTarget() + filename;
		var action = Repos.getWebapp() + 'open/?target=' +  encodeURIComponent(path);
		action = action + '&base=' + Repos.getBase();
		window.location.href = action;
		return false;
	});
});
*/
