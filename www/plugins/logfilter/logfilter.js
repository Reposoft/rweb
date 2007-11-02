/**
 * Repos history filter (c) 2006 repos.se
 */

Repos.service('open/log/', function() {
	var t = Repos.getTarget();
	if (t.charAt(t.length-1)=='/') return;
	repos_logfilter(t);
});

/**
 * @param {Object} target The url to focus on in the current history page
 */
function repos_logfilter(target) {
	var command = $('<a id="showpaths" name="showpaths"/>').append('show related changes');
	command.appendTo($('#commandbar'));
	command.click(repos_logfilter_showall);

	$('div.log-A, div.log-M, div.log-D').each( function() {
		var p = $('.file, .folder', this).text();
		if (target && p == target) return;
		$(this).hide();
	} );
}

function repos_logfilter_showall() {
	$('div.row').show();
	$('#showpaths').hide();
}
