/**
 * Repos history filter (c) 2006 repos.se
 */
Repos.service('open/log/', function() {
	var logentries = $('.logentry').reposCollapsable();
	var all = $('<a id="expandall"/>').css('cursor', 'pointer').append('Expand all');
	$('#commandbar').append(all);
	all.click(function() {
		var t = $(this);
		if (t.is('#expandall')) {
			logentries.reposCollapsable('expanded');
			t.attr('id', 'collapseall').text('Collapse all');
		} else {
			logentries.reposCollapsable('collapsed');
			t.attr('id', 'expandall').text('Expand all');
		}
	});
});
