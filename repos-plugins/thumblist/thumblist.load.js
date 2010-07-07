
var reposThumbSupported = function(name) {
	// definition from the thumbnails plugin
	return Repos.thumbnails.match.test(name);
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
	var name = a.text();	
	if (!reposThumbSupported(name)) return;
	var target = Repos.getTarget() + name;
	var thumb = Repos.thumbnails.getSrc(target, peg, false); // paths are at HEAD when in index
	var viewUrl = $('a[id^=view:]', item).attr('href').replace('/?', '/file/?');
	
	//reposThumbFormatGallerificStyle(item, a, name, thumb);
	reposThumbFormatAsList(item, a, name, thumb, viewUrl);
	item.addClass('thumbnail');
};

var reposThumblistOnLoad = function() {
	$('.index > li').each(function() {
		reposThumbFromListItem.apply(this);
	});
};

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
		$(window).trigger('scroll'); // TODO better isolation, trigger only inview check
	});
};

/**
 * Simple layout in list, using padding + background image.
 * @param item list item
 * @param a the link to the image
 * @param name image label
 * @param thumb URL to thumbnail
 */
var reposThumbFormatAsList = function(item, a, name, thumb, viewUrl) {
	// uses CSS
	a.css({
		backgroundImage: 'url("' + thumb + '")'
	});
	// same image click behavior as InList below
	a.click(function(ev) {
		var imgw = 150; // clickable area's width in pixels, hardcoded to match transform settings in thumbnail and CSS
		var clickx = ev.pageX;
		var imgx = $(this).offset().left; // x coordinate where the image starts
		if (clickx < imgx + imgw) {
			ev.preventDefault();
			location.href = viewUrl;
		}
	});
};

var reposThumbFormatGallerificStyle = function(item, a, name, thumb) {
	var img = $('<img/>').attr('src', thumb).attr('alt', name);
	a.empty().append(img);
	
	var div = $('<div/>').text(name).appendTo(item);
	$('.actions', item).appendTo(div);	
};

Repos.service('index/', reposThumblistOnDetails);

var reposThumbFromTableRow = function(row) {
	
	var acell = $('td:first', row).next();
	var newcell = $('<td/>').addClass('thumbnail').insertBefore(acell);
	
	// assuming tags from details plugin
	// Repository browsing is HEAD but revisions from details are last-changed
	// and changes might have occured at old urls so we must use peg revision
	var peg = $('.revision', row).text();
	
	var a = $('> a', acell);
	var name = a.text();
	if (reposThumbSupported(name)) {
		// not tested with funny characters
		var target = Repos.getTarget() + name;
		var thumbUrl = Repos.thumbnails.getSrc(target, peg, false); // paths are at HEAD when in index
		var thumb = $('<img/>').attr('src', thumbUrl).css('border', '0');
		var viewUrl = a.attr('href').replace('/?', '/file/?');
		var view = $('<a/>').attr('href', viewUrl);
		view.append(thumb).appendTo(newcell);
	}
};

var reposThumblistInListHide = function() {
	$('td.thumbnail').hide();
	$(this).html('show&nbsp;thumbnails').one('click', reposThumblistInListShow);
};

var reposThumblistInListShow = function() {
	$('td.thumbnail').show();
	$(this).html('hide&nbsp;thumbnails').one('click', reposThumblistInListHide);
};

var reposThumblistInList = function() {
	$('<td>&nbsp;</td>').addClass('thumbnail').insertAfter('thead td:first');
	$('.index tbody tr')
		//.inviewOne(
		.each(
				function() {
					reposThumbFromTableRow(this);
				});
	$(this).html('hide&nbsp;thumbnails').one('click', reposThumblistInListHide);
};

var reposThumblistInListButton = function() {
	$('<a id="listthumbs" href="javascript:void(0)">load&nbsp;thumbnails</a>').appendTo('#commandbar').one('click', reposThumblistInList);
};

Repos.service('open/list/', reposThumblistInListButton);
