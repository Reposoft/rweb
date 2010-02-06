
var reposThumbGetUrl = function(target, rev) {
	var url = '/repos-plugins/thumbnails/convert/?target=';
	url = url + encodeURIComponent(target);
	url = url + '&base=' + Repos.getBase();
	if (typeof rev != 'undefined' && rev) {
		url = url + '&rev=' + rev;
	}
	return url;
};

var reposThumbSupported = function(link) {
	// definition from the thumbnails plugin
	return Repos.thumbnails.match.test(link.attr('href'));
};

var reposThumbFromListItem = function(item) {
	item = $(item || this);
	
	// assuming tags from details plugin
	var rev = $('.revision', this).text();
	if (!rev) {
		window.console && console.log('revision not found for row', this);
	}
	
	var a = $('> a', item);
	if (!reposThumbSupported(a)) return;
	var name = a.text();
	var target = Repos.getTarget() + name;
	var thumb = reposThumbGetUrl(target, rev);
	
	//reposThumbFormatGallerificStyle(item, a, name, thumb);
	reposThumbFormatAsList(item, a, name, thumb);
};

var reposThumblistAll = function() {
	$('.index > li').each(function() {
		reposThumbFromListItem.apply(this);
	});
};

var reposThumblistOnDetails = function() {
	$('.index > li').bind('repos-details-displayed', function(ev) {
		console.log('details', this);
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

Repos.service('index/', reposThumblistOnDetails);
