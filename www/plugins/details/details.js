/**
 * Repos details plugin (c) 2006-2009 repos.se
 *
 * @author Staffan Olsson (solsson)
 */

(function($) {

function button() {
	var index = $('.index li');
	var c = $('<span id="showdetails">');
	if (index.size() > 0) {
		c = $('<a id="showdetails" name="showdetails"/>');
		c.click( function() {
			index.reposDetails();
		} );
	}
	c.html('show&nbsp;details').appendTo('#commandbar');
}

Repos.service('index/', button);

$.fn.reposDetails = function(s) {
	
	s = $.extend({
		url: Repos.getWebapp() + 'open/list/?target=',
		target: Repos.getTarget(),
		encode: true
	}, s);
	// encode target for use as parameter
	if (s.encode) s.target = encodeURIComponent(s.target);
	
	details_repository(s.target, s.url+s.target);
	
};

// scope for unit tests, internally we still use the function var name in current scope
var that = $.fn.reposDetails;

// show details for an existing element (page designer sets target with the 'title' attribute)
var details_read = that.details_read = function() {
	var e = $('body').find('div.details');
	if (e.size() > 0) {
		$.get(Repos.getWebapp() + 'open/list/?target='+encodeURIComponent(e.attr("title")), function(xml) {
				details_write(e, $('/lists/list/entry', xml)); });
	}
}

/**
 *
 * @param e jQuery element to write details to
 * @param entry the entry node from svn list --xml 
 */
var details_write = that.details_write = function(e, entry) {
	entry.each(function(){
		$('.filename', e).append($('name', this).text());
		var noaccess = details_isNoaccess(this);
		if (noaccess) {
			e.addClass('noaccess');
			$('.username', e).addClass('unknown').append('(unknown)'); // temporary solution
		} else {
			$('.username', e).append($('commit>author', this).text());
			$('.datetime', e).append($('commit>date', this).text()).dateformat();
		}
		$('.revision', e).append($('commit', this).attr('revision'));
		var folder = details_isFolder(this);
		if (!folder) {
			var size = $('size', this).text();
			$('.filesize', e).append(size).byteformat();
			$('.filesize', e).attr('title', size + ' bytes');
		}
		if (details_isLocked(this)) details_writeLock(e, this);
	});
	e.show();
}

var details_writeLock = that.details_writeLock = function(e, entry) {
	var lock = $('lock', entry);
	e.addClass('locked');
	// addtags does not create lock spans
	var s = $('lock', e);
	if (s.size() == 0) s = $('<div class="lock"></div>').appendTo(e);
	s.append('<span class="username">'+ $('owner', lock).text() +'</span>&nbsp;');
	$('<span class="datetime">'+ $('created', lock).text() +'</span>&nbsp;').appendTo(s).dateformat();
	s.append(' <span class="message">'+ $('comment', lock).text() +'</span>');
}

var details_repository = that.details_repository = function(path, url) {
	$.ajax({
		type: 'GET',
		url: url,
		dataType: 'xml',
		error: function() { $('#showdetails').removeClass('loading').text('error'); },
		success: function(xml){
			$('lists>list>entry', xml).each(function() {
				var name = $('name', this).text();
				if (this.getAttribute('kind')=='dir') name = name + '/';
				// note that there might be id conflicts in repos.xsl pages due to limitations in name escape
				var row = new ReposFileId(name).find('row');
				if (row == null) {
					if (console && console.log) console.log('No row found for', name, new ReposFileId(name).get());
					return; // silently skip items that can't be found
				}
				row = $(row);
				details_addtags(row);
				//$('.details',row).hide();
				details_write(row, $(this));
				row.trigger('repos-details-displayed');
			});
			$('.details').show();
			$('#showdetails').removeClass('loading').text('refresh details');
		}
	});
}

var details_isFolder = that.details_isFolder = function(entry) {
	return $('size', entry).size() == 0;
}

var details_isLocked = that.details_isLocked = function(entry) {
	return $('lock', entry).size() > 0;
}

var details_isNoaccess = that.details_isNoaccess = function(entry) {
	// commit>author may be empty for anonymous commit, but date seems to be empty only on no read access
	return $('commit>date', entry).size() == 0;
}

/**
 * Adds empty placeholders for common detail entries (except name, which is probably displayed already)
 * @param e jQuery element to add to
 */
var details_addtags = that.details_addtags = function(e) {
 	$(e).find('div.details, span.lock').remove(); // allow refresh
	e.append('<div class="details"><span class="revision"></span><span class="datetime"></span><span class="username"></span><span class="filesize"></span></div>');
}

var detailsToggle = that.detailsToggle = function() {
	$('#commandbar #showdetails').addClass('loading');
	details_repository();
}

})(jQuery);
