
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
	var name = a.text();
	var target = Repos.getTarget() + name;
	var thumb = reposThumbGetUrl(target);
	
	//reposThumbFormatGallerificStyle(item, a, name, thumb);
	reposThumbFormatAsList(item, a, name, thumb);
};

var reposThumblist = function() {
	$('.index > li').each(function() {
		reposThumbFromListItem.apply(this);
	});
};

var reposThumbFormatAsList = function(item, a, name, thumb) {
	a.css({
		display: 'block',
		paddingLeft: 160,
		paddingTop: 50,
		paddingBottom: 50,
		backgroundPosition: 'left center',
		backgroundImage: 'url("' + thumb + '")'
	});
};

var reposThumbFormatGallerificStyle = function(item, a, name, thumb) {
	var img = $('<img/>').attr('src', thumb).attr('alt', name);
	a.empty().append(img);
	
	var div = $('<div/>').text(name).appendTo(item);
	$('.actions', item).appendTo(div);	
};

Repos.service('index/', reposThumblist);
