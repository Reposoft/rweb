
var reposThumbSupported = function(link) {
	// definition from the thumbnails plugin
	return Repos.thumbnails.match.test(link.attr('href'));
};

var reposThumbFromListItem = function(item) {
	item = $(item || this);
	
	// assuming tags from details plugin
	// Repository browsing is HEAD but revisions from details are last-changed
	// and changes might have occured at old urls so we must use peg revision
	var peg = $('.revision', this).text();
	if (!peg) {
		window.console && console.error('revision not found for row', this);
		peg = 'HEAD';
	}
	
	var a = $('> a', item);
	if (!reposThumbSupported(a)) return;
	var name = a.text();
	var target = Repos.getTarget() + name;
	var thumb = Repos.thumbnails.getSrc(target, peg, false); // paths are at HEAD when in index
	
	//reposThumbFormatGallerificStyle(item, a, name, thumb);
	//reposThumbFormatAsList(item, a, name, thumb);
	reposThumbFormatAsListOnView(item, a, name, thumb);
	item.addClass('thumbnail');
};

var reposThumblistOnLoad = function() {
	$('.index > li').each(function() {
		reposThumbFromListItem.apply(this);
	});
};

var reposThumblistOnDetails = function() {
	$('.index > li').bind('repos-details-displayed', function(ev) {
		reposThumbFromListItem.apply(this);
	});
};

/**
 * Delays thumbnail loading until the item is inside the viewport.
 */
var reposThumbFormatAsListOnView = function(item, a, name, thumb) {
	a.inviewOne(function() {
		reposThumbFormatAsList(item, a, name, thumb);
	});
};

/**
 * Simple layout in list, using padding + background image.
 * @param item list item
 * @param a the link to the image
 * @param name image label
 * @param thumb URL to thumbnail
 */
var reposThumbFormatAsList = function(item, a, name, thumb) {
	var h = 120;
	a.css({
		display: 'block',
		paddingLeft: 160,
		height: h,
		backgroundPosition: 'left center',
		backgroundImage: 'url("' + thumb + '")'
	});
	var li = a.parent();
	li.css({
		height: h + 2
	});
	$('> .actions', li).css({
		marginTop: 18
	});
	$('> .details', li).css({
		marginTop: 36 - h
	});
};

var reposThumbFormatGallerificStyle = function(item, a, name, thumb) {
	var img = $('<img/>').attr('src', thumb).attr('alt', name);
	a.empty().append(img);
	
	var div = $('<div/>').text(name).appendTo(item);
	$('.actions', item).appendTo(div);	
};

Repos.service('index/', reposThumblistOnDetails);
