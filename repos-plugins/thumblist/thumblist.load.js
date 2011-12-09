
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
	//var viewUrl = $('a[id^="view\\:"]', item).attr('href').replace('/?', '/file/?');
	
	//reposThumbFormatGallerificStyle(item, a, name, thumb);
	reposThumbFormatAsList(item, a, name, thumb);
	item.addClass('thumbnail');
};

var reposThumblistOnLoad = function() {
	$('.index > li').each(function() {
		reposThumbFromListItem.apply(this);
	});
};

/**
 * Show thumbnails when repos details plugin is activated to shoẃ list info
 */
var reposThumblistOnDetails = function() {
	$('.index > li').bind('repos-details-displayed', function(ev) {
		//reposThumbFromListItem.apply(this);
		// Using the inview plugin to load when in viewport
		$(this).one('inview', function() {
			reposThumbFromListItem.apply(this);
		});
	});
	// trigger first inview check
	$('.index').bind('repos-details-completed', function() {
		$(window).scroll(); // TODO better isolation, trigger only inview check
	});
};

/**
 * Show thumbnails immediately.
 */
var reposThumblistOnInview = function() {
	$('.index > li').one('inview', function() {
		reposThumbFromListItem.apply(this);
	});
	// trigger first inview check
	$(window).trigger('scroll'); // TODO better isolation, trigger only inview check	
};

/**
 * Simple layout in list, using padding + background image.
 * @param item list item
 * @param a the link to the image
 * @param name image label
 * @param thumb URL to thumbnail
 */
var reposThumbFormatAsList = function(item, a, name, thumb) {
	// uses CSS
	a.css({
		backgroundImage: 'url("' + thumb + '")'
	});
};

var reposThumbFormatGallerificStyle = function(item, a, name, thumb) {
	var img = $('<img/>').attr('src', thumb).attr('alt', name);
	a.empty().append(img);
	
	var div = $('<div/>').text(name).appendTo(item);
	$('.actions', item).appendTo(div);	
};

Repos.service('index/', function() {
	var base = Repos.getBase();
	if (base == 'clipart' || base == 'images' || base == 'gallery') {
		reposThumblistOnInview();
	} else {
		reposThumblistOnDetails();
	}
});
