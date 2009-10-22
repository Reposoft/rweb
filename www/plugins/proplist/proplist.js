
if (typeof(Repos) == 'undefined') Repos = {};

Repos.proplist = {};

$(document).ready( function() {
	Repos.proplist.init();
} );

// --- Event definitions ---
$().bind('repos-proplist-loaded', function(event, proplistParent){});
// ---

Repos.proplist.init = function() {
	$('.proplist').each( function() {
		var target = Repos.getTarget(this);
		//var rev = Repos.getRevision(this); // method does not exist yet
		var rev = $('.revision:first').text();
		if (target) Repos.proplist.addClick(this, target, rev);
	} );
};

Repos.proplist.addClick = function(container, target, rev) {
	var call = Repos.url+'open/proplist/?target='+target + (rev ? '&rev=' + rev : '');
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
					$().trigger('repos-proplist-loaded', [e.parent()]);
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
	} else if (!json.proplist || !json.proplist.length) {
		jqElem.append('<p>No properties set</p>');
	} else {
		list = $('<dl class="properties"><lh>Versioned properties</lh></dl>');
		var keys = [];
		for (var prop in json.proplist) {
			keys.push(prop);
		}
		keys.sort(); // TODO case insensitive
		for (var i = 0; i < keys.length; i++) {
			var prop = keys[i];
			list.append('<dt>'+prop+'</dt><dd>'+json.proplist[prop]+'</dd>');
		}
		jqElem.append(list);
	}
};

