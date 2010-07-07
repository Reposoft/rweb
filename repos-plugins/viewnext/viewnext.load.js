/**
 * Add actions to view next and previous item from the directory listing.
 */
(function() {
	
	var _folder, _target;
	
	var serviceUrl = function(service, target) {
		var url = '/repos-web/' + service + '?target=' + encodeURIComponent(target) + '&base=' + Repos.getBase();
		if (Repos.isRevisionRequested()) url += '&rev=' + Repos.getRevisionRequested();
		return url;
	};
	
	var action = function(name) {
		var url = serviceUrl('open/file/', _folder + name);
		var a = $('<a/>').attr('href', url).attr('title', name);
		return a;
	};
	
	var getMatchingNamesSorted = function(listJson, matchFunction) {
		var n = [];
		for (k in listJson) {
			if (listJson.hasOwnProperty(k)) {
				if (matchFunction(k)) {
					n.push(k);
				}
			}
		}
		return n.sort();
	};
	
	var add = function(options, listJson) {
		var settings = $.extend({
			currentName: _target.split('/').pop(),
			filenameFilter: /.*/
		}, options);
		
		var filter = function(name) {
			if (!settings.filenameFilter) return true;
			return settings.filenameFilter.test(name);
		};
		
		var names = getMatchingNamesSorted(listJson.list, filter);
		var current = names.indexOf(settings.currentName);
		if (current == -1) {
			return; // error
		}
		
		// currently assumes that there is a match, if not prev will be the last item
		var insert = function(action) {
			$('#commandbar').append(action);
		};
		
		if (current > 0) {
			var prev = names[current - 1];
			insert(action(prev, listJson.list[prev]).text('previous').attr('id', 'viewprev'));
		}
		if (current < names.length - 1) {
			var next = names[current + 1];
			insert(action(next, listJson.list[next]).text('next').attr('id', 'viewnext'))
		}
	};
	
	Repos.service('open/file/', function() {
		
		var options = {};
		if ($('body').is('.image')) {
			options.filenameFilter = new RegExp('\.(' + Repos.thumbnails.filetypes + ')$', 'i');
		}
		
		_target = Repos.getTarget();
		_folder = _target.substring(0, _target.lastIndexOf('/') + 1);
		var listJsonUrl = serviceUrl('open/json/', _folder) + '&depth=files';
		$.ajax({
			url: listJsonUrl,
			dataType: 'json',
			success: function(json) {
				add(options, json);
			}
		});
		
	});
	
})();