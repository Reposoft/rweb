
if (typeof(Repos) == 'undefined') Repos = {};

Repos.proplist = {};

$(document).ready( function() {
	Repos.proplist.init();
} );

Repos.proplist.init = function() {
	$('.proplist').each( function() {
		var target = Repos.getTarget(this);
		if (target) Repos.proplist.addClick(this, target);
	} );
};

Repos.proplist.addClick = function(container, target) {
	var call = Repos.url+'open/proplist/?target='+target;
	var link = $('<a href="'+call+'">Versioned properties</a>')
		.click( function() {
			var e = $(this);
			e.text(' ...').addClass('loading');
			$.ajax({
				type: 'GET',
				url: e.attr('href'),
				dataType: 'json',
				success: function(json) {
					Repos.proplist.present(e.parent(), json);
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
	if (!json.proplist) {
		jqElem.append('<p>No property list found</p>');
	} else if (json.proplist.length == 0) {
		jqElem.append('<p>No properties set</p>');
	} else {
		list = $('<dl><lh>Versioned properties</lh></dl>');
		for (var prop in json.proplist) {
			list.append('<dt>'+prop+'</dt><dd>'+json.proplist[prop]+'</dd>');
		}
		jqElem.append(list);
	}
};

