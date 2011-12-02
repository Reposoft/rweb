
if (typeof(Repos) == 'undefined') Repos = {};

Repos.proplist = {};

$(document).ready( function() {
	Repos.proplist.init();
} );

// --- Event definitions ---
$(document).bind('repos-proplist-loaded', function(event, proplistParent){});
// ---

Repos.proplist.init = function() {
	$('.proplist').each( function() {
		var target = Repos.getTarget(this);
		// This assumes that no changes to target are committed between page view and proplist request
		// and avoids a problem with getRevision returning last-changed when url is HEAD
		var rev = Repos.isRevisionRequested() && Repos.getRevision();
		if (target) Repos.proplist.addClick(this, target, rev);
	} );
};

Repos.proplist.addClick = function(container, target, rev) {
	var call = Repos.url+'open/proplist/?target=' + 
			encodeURIComponent(target) + (rev ? '&rev=' + rev : '');
	var link = $('<a class="action-load" href="'+call+'">Versioned properties</a>')
		.click( function() {
			var e = $(this);
			e.text(' ...').addClass('loading');
			$.ajax({
				type: 'GET',
				url: e.attr('href'),
				dataType: 'json',
				success: function(json) {
					Repos.proplist.present(e.parent(), json);
					$(document).trigger('repos-proplist-loaded', [e.parent()]);
					e.remove();
				},
				error: function() {
					$(container).append('Failed to read properties');
					e.remove();
				} 
				});
			return false;
		} );
	$(container).append(link);
};

Repos.proplist.present = function(jqElem, json) {
	if (!json) {
		jqElem.append('<p>No property list found</p>');
	} else if (!json.proplist) {
		jqElem.append('<p>No properties set</p>');
	} else {
		var list = $('<dl class="properties"><lh>Versioned properties</lh></dl>');
		var keys = [];
		for (var prop in json.proplist) {
			keys.push(prop);
		}
		keys.sort(); // TODO case insensitive, or sort in backend
		for (var i = 0; i < keys.length; i++) {
			var prop = keys[i];
			$('<dt/>').text(prop).appendTo(list);
			// this must preserve newlines so that the value can be accessed from other plugins
			$('<dd/>').text(json.proplist[prop]).appendTo(list);
		}
		jqElem.append(list);
	}
};

