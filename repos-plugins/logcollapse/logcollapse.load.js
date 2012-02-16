/**
 * Repos history filter (c) 2006 repos.se
 */
Repos.service('open/log/', function() {
	var logentries = $('.logentry').reposCollapsable();
	var all = $('<a id="expandall"/>').css('cursor', 'pointer').append('Expand all');
	$('#commandbar').append(all);
	all.toggle(function() {
		logentries.reposCollapsable('expanded');
		$(this).attr('id', 'collapseall').text('Collapse all');
	}, function() {
		logentries.reposCollapsable('collapsed');
		$(this).attr('id', 'expandall').text('Expand all');
	});	
});
