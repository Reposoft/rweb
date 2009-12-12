/**
 * Repos history filter (c) 2006 repos.se
 */
Repos.service('open/log/', function() {
	reposMakeCollapsible('.logentry', 'h3')
});

//quite generic for divs with headline
function reposMakeCollapsible(parents, clickable) {
	$(parents).addClass('collapsed');
	$(parents + ' > ' + clickable).click(function() {
		$(this).parent().toggleClass('expanded').toggleClass('collapsed');
	});
	var all = $('<a id="expandall"/>').addClass('action').append('Expand all');
	$('#commandbar').append(all);
	all.toggle(function() {
		$(parents).addClass('expanded').removeClass('collapsed');
		$(this).attr('id', 'collapseall').text('Collapse all');
	}, function() {
		$(parents).addClass('collapsed').removeClass('expanded');
		$(this).attr('id', 'expandall').text('Expand all');
	});
}
