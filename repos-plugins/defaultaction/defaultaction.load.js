/**
 * Open details page by default when clicking a file,
 * but preserve the real URL for right clicking.
 */
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

/**
 * Same for file nodes in Repos Tree
 */
Repos.service('repos-plugins/tree/', function() {
	$().bind("repos-tree-item-added", function(ev, item, target, base) {
		var action = Repos.getWebapp() + 'open/?target=' +  encodeURIComponent(target);
		if (typeof base != 'undefined' && base) {
			action = action + '&base=' + base;
		}
		$(item).filter('.file').children('a').click(function(ev) {
			ev.stopPropagation();
			window.location.href = action;
			return false;
		});
	});
});
