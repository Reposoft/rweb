/**
 * Dynamic features for multiple selection in repos list.
 * 
 * - When selecting/deselecting folder, also tick subitems.
 * - Add buttons to select all and deselect all (TODO).
 */
(function reposListSelect() {
	
	// helper functsions
	
	var isListRecursive = function() {
		return /[?&]depth=infinity/.test(location.search);
	};
	
	var link = function(row) {
		return row.find('a:first');
	};
	
	var path = function(row) {
		return link(row).text();
	};
	
	var checkbox = function(row) {
		return row.find('> td:first > :checkbox');
	};
	
	var selectSubItems = function() {
		// Iterate possible subitems and stop on forst non-match
		// This is easy as long as list storting is not customizable
		var selected = $(this).is(':checked');
		var frow = $(this).parent().parent(); 
		var fpath = path(frow) + '/'; // svn list paths are given without trailing slash
		var flen = fpath.length;
		// home made iterator that stops when a row is not a subitem
		var current = frow;
		var next = function() {
			current = current.next();
			var p = path(current); 
			return p.length > flen && p.substring(0, flen) == fpath;
		};
		while (next()) {
			if (selected) {
				checkbox(current).attr('checked', 'checked').one('change', deselectParents);
			} else {
				checkbox(current).removeAttr('checked');
			}
		}
	};
	
	var deselectParents = function() {
		var current = $(this).parent().parent();
		var cpath = path(current);
		for ( ;current.length ;current = current.prev()) {
			if (!current.is('.row-folder')) continue;
			var p = path(current);
			if (cpath.length > p.length && cpath.substring(0, p.length + 1) == p + '/') {
				checkbox(current).removeAttr('checked');
			}
		};
	};
	
	var onFolderSelectChange = function() {
		selectSubItems.call(this);
	};
	
	var onList = function() {
		if (isListRecursive()) {
			$('a.folder').each(function() {
				checkbox($(this).parent().parent()).change(onFolderSelectChange);
			});
		}
	};
	
	Repos.service('open/list/', onList);
	
})();
