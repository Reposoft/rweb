/**
 * Repos details plugin (c) 2006 repos.se
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

// show details for an existing element (page designer sets target with the 'title' attribute)
function details_read() {
	var e = $('body').find('div.details');
	if (e.size() > 0) {
		$.get('/repos/open/list/?target='+encodeURIComponent(e.attr("title")), function(xml) {
				details_write(e, $('/lists/list/entry', xml)); });
	}
}

/**
 *
 * @param e jQuery element to write details to
 * @param entry the entry node from svn list --xml 
 */
function details_write(e, entry) {
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

function details_writeLock(e, entry) {
	var lock = $('lock', entry);
	e.addClass('locked');
	// addtags does not create lock spans
	var s = $('lock', e);
	if (s.size() == 0) s = $('<span class="lock"></span>').appendTo(e);
	s.append('<span class="username">'+ $('owner', lock).text() +'</span>&nbsp;');
	s.append('<span class="datetime">'+ $('created', lock).text() +'</span>&nbsp;');
	s.append(' <span class="message">'+ $('comment', lock).text() +'</span>');
}

function details_repository(path, url) {
	$.ajax({
		type: 'GET',
		url: url,
		dataType: 'xml',
		error: function() { $('#showdetails').removeClass('loading').text('error'); },
		success: function(xml){
			$('lists>list>entry', xml).each(function() {
				var name = $('name', this).text();
				if (this.getAttribute('kind')=='dir') name = name + '/';
				var row = new ReposFileId(name).find('row');
				if (row == null) return; // silently skip items that can't be found
				row = $(row);
				details_addtags(row);
				//$('.details',row).hide();
				details_write(row, $(this));
			});
			$('.details').show();
			$('#showdetails').removeClass('loading').text('refresh details');
		}
	});
}

function details_isFolder(entry) {
	return $('size', entry).size() == 0;
}

function details_isLocked(entry) {
	return $('lock', entry).size() > 0;
}

function details_isNoaccess(entry) {
	// commit>author may be empty for anonymous commit, but date seems to be empty only on no read access
	return $('commit>date', entry).size() == 0;
}

/**
 * Adds empty placeholders for common detail entries (except name, which is probably displayed already)
 * @param e jQuery element to add to
 */
function details_addtags(e) {
 	$(e).find('div.details, span.lock').remove(); // allow refresh
	e.append('<div class="details"><span class="revision"></span><span class="datetime"></span><span class="username"></span><span class="filesize"></span></div>');
}

function detailsToggle() {
	$('#commandbar #showdetails').addClass('loading');
	details_repository();
}

})(jQuery);
