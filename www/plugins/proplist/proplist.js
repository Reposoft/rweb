
Repos.proplist = {};

$(document).ready( function() {
	Repos.proplist.init();
} );

Repos.proplist.init = function() {
	$('.proplist').each( function() {
		var target = Repos.proplist.getTarget(this);
		if (target) Repos.proplist.addClick(this, target);
	} );
};

Repos.proplist.getTarget = function(elem) {
	if ($(elem).attr('title')) return $(elem).attr('title');
	return $(elem).parent().attr('title');
};

Repos.proplist.addClick = function(container, target) {
	var call = Repos.url+'open/proplist/?target='+target;
	var link = $('<a href="'+call+'">Versioned properties</a>')
		.click( function() {
			var e = $(this);
			e.text(' ...').addClass('loading');
			$.getJSON(e.attr('href'), function(json) {
				Repos.proplist.present(e.parent(), json);
				e.remove();
			} );
			return false;
		} );
	$(container).append(link);
};

Repos.proplist.present = function(jqElem, json) {
	if (!json.proplist) {
		jqElem.append('<p>Could not read properties</p>');
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

