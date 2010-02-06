
var reposThumbGetUrl = function(target, rev) {
	// TODO how do we get the revision number for proper caching?
	return '/repos-plugins/thumbnails/convert/?target=' +
		encodeURIComponent(target) + '&base=' + Repos.getBase();
};

var reposThumbSupported = function(link) {
	// definition from the thumbnails plugin
	return Repos.thumbnails.match.test(link.attr('href'));
};

var reposThumbFromListItem = function(item) {
	item = $(item || this);
	
	var a = $('> a', item);
	if (!reposThumbSupported(a)) return;
	var text = a.text();
	var target = Repos.getTarget() + text;
	console.log(a, text, target);
	
	// Gallerific style
	var img = $('<img/>').attr('src', reposThumbGetUrl(target)).attr('alt', text);
	a.empty().append(img);
	
	var div = $('<div/>').text(text).appendTo(item);
	$('.actions', item).appendTo(div);
	
};

var reposThumblist = function() {

	$('.index > li').each(function() {
		reposThumbFromListItem.apply(this);
	});
	
};

Repos.service('index/', reposThumblist);
