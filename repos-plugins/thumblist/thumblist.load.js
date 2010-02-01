
var reposThumbGetUrl = function(target, rev) {
	// TODO how do we get the revision number for proper caching?
	return '/repos-plugins/thumbnails/convert/?target=' +
		encodeURIComponent(target) + '&base=' + Repos.getBase();
};

var reposThumbFromListItem = function(item) {
	item = $(item || this);
	
	var a = $('> a', item);
	var text = a.text();
	var target = Repos.getTarget() + text;
	console.log(a, text, target);
	
	// Gallerific style
	var img = $('<img/>').attr('src', reposThumbGetUrl(target)).attr('alt', text);
	a.empty().append(img);
	
	var div = $('<div/>').text(text).appendTo(item);
	
};

var reposThumblist = function() {

	$('.index > li').each(function() {
		reposThumbFromListItem.apply(this);
	});
	
};

//Repos.service('index/', reposThumblist);
