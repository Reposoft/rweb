/**
 * Repos details plugin (c) 2006-2012 repos.se
 *
 * Replaces the "list" command with dynamically loaded details.
 *  
 * @author Staffan Olsson (solsson)
 */

(function($) {

var button = function() {
	var index = $('.index li');
	var button = $('#commandbar #list');
	if (button.size() == 0) {
		button = $('<a id="list"/>');
	}
	button.attr('href', '#view=list');
	// detect true change in hash param
	Repos.onUiParam('view', function(mode) {
		// currently we have only one mode and can only switch it on
		index.reposDetails();
	});	
};

Repos.service('index/', button);

$.fn.reposDetails = function(s) {

	$('a.folder', this).add('#parent').each(function() {
		$(this).attr('href', $(this).attr('href') + '#view=list');
	});
	
	s = $.extend({
		url: Repos.getWebapp() + 'open/list/?base=' + Repos.getBase() + '&target=',
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
};

/**
 *
 * @param e jQuery element to write details to
 * @param entry the entry node from svn list --xml 
 */
var details_write = that.details_write = function(e, entry) {
	entry.each(function(){
		$('.filename', e).append($('name', this).text());
		if (details_isNoaccess(this)) {
			e.addClass('noaccess');
			$('.username', e).append('(no access)');
		} else {
			if ($('commit>author', entry).size() == 0) {
				$('.username', e).append('(anonymous)');
			} else {
				$('.username', e).append($('commit>author', this).text());
			}
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
		details_writeThumb(e, this);
	});
	e.show();
};

var details_writeLock = that.details_writeLock = function(e, entry) {
	var lock = $('lock', entry);
	e.addClass('locked');
	// addtags does not create lock spans
	var s = $('lock', e);
	if (s.size() == 0) s = $('<div class="details lock"></div>').appendTo(e);
	s.append('<span class="username">'+ $('owner', lock).text() +'</span>&nbsp;');
	$('<span class="datetime">'+ $('created', lock).text() +'</span>&nbsp;').appendTo(s).dateformat();
	s.append(' <span class="message">'+ $('comment', lock).text() +'</span>');
};

var details_writeThumb = that.details_writeThumb = function(e, entry) {
	var thumb = $('<span/>').addClass('thumbnail').prependTo(e);
	
	var src = $('a:first', e).attr('href').replace('rweb=details', 'rweb=t.tiny');
	console.log(e, entry, thumb, src);
	if (!src) return;
	
	var img = $("<img />").addClass('thumbnail').attr('src', src).load(function() { // alt="Creating thumbnail..."
		if (!this.complete
				|| typeof this.naturalWidth == "undefined"
				|| this.naturalWidth == 0) {
			console.warn('No error message, no image', src);
		} else {
			thumb.append(img);
		}
	}).error(function() {
		// normally status=415, error not called for status=500 which is good because we want to show the error thumbnail instead
		$(this).hide(); 
	});
};

var details_repository = that.details_repository = function(path, url) {
	$('#commandbar #showdetails').addClass('loading');
	$.ajax({
		type: 'GET',
		url: url,
		dataType: 'xml',
		error: function() { $('#showdetails').removeClass('loading').text('error'); },
		success: function(xml){
			$('.index').addClass('table'); // TODO locate the list that details are appended to
			$('.itemextend').remove(); // TODO deactivate browsedetails for real
			$('.index li').css('display', 'table-row'); // TODO why doesn't the CSS style happen?
			// TODO thumbnail
			$('lists>list>entry', xml).each(function() {
				var name = $('name', this).text();
				if (this.getAttribute('kind')=='dir') name = name + '/';
				// note that there might be id conflicts in repos.xsl pages due to limitations in name escape
				var row = new ReposFileId(name).find('row');
				if (row == null) {
					window.console && console.log('No row found for', name, new ReposFileId(name).get());
					return; // silently skip items that can't be found
				}
				row = $(row);
				details_addtags(row);
				//$('.details',row).hide();
				details_write(row, $(this));
				row.trigger('repos-details-displayed');
			});
			$('.details').show();
			$('#showdetails').removeClass('loading');//.text('refresh&nbsp;details');
			$('.index').trigger('repos-details-completed');
		}
	});
};

var details_isFolder = that.details_isFolder = function(entry) {
	return $('size', entry).size() == 0;
};

var details_isLocked = that.details_isLocked = function(entry) {
	return $('lock', entry).size() > 0;
};

var details_isNoaccess = that.details_isNoaccess = function(entry) {
	// commit>author may be empty for anonymous commit, but date seems to be empty only on no read access
	return $('commit>date', entry).size() == 0;
};

/**
 * Adds empty placeholders for common detail entries (except name, which is probably displayed already)
 * @param e jQuery element to add to
 */
var details_addtags = that.details_addtags = function(e) {
 	$(e).find('div.details, span.lock').remove(); // allow refresh
	e.append('<span class="details revision"></span><span class="details datetime"></span><span class="details username"></span><span class="details filesize"></span>');
};

})(jQuery);
