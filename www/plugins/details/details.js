/**
 * Repos details plugin (c) 2006 repos.se
 *
 * Simply add this script to a page together with a div class="details" or a #fullpath and row:fileId entries.
 * @author Staffan Olsson (solsson)
 * Maybe this should be refactored to classes. Can be done anytime because these functions are currently not called from anywere.
 * $Id$
 */

$(document).ready(function() {
	details_read();
	// for repository listings, add details button
	if ($('.repository #fullpath').size() > 0) {
		details_button();	
	}
});

function details_button() {
	var command = $('<span id="showdetails">');
	if ($('.row').size() > 0) {
		command = $('<a id="showdetails" name="showdetails"/>');
		command.click(function() { detailsToggle(this); });
	}
	command.append('show details').appendTo('#commandbar>*:first');
}

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
			$('.username', e).append('(no access)'); // temporary solution
		} else {
			$('.username', e).append($('commit>author', this).text());
			$('.datetime', e).append($('commit>date', this).text());
		}
		$('.revision', e).append($('commit', this).attr('revision'));
		var folder = details_isFolder(this);
		if (!folder) {
			var size = $('size', this).text();
			$('.filesize', e).append(details_formatSize(size));
			$('.filesize', e).attr('title', size + ' bytes');
		}
		if (details_isLocked(this)) details_writeLock(e, this);
		// if the dateformat plugin is present, do format
		$('.datetime', e).each(function() {
			if (typeof('Dateformat')!='undefined') {
				(new Dateformat()).formatElement(this);
			}
		});
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

function details_repository() {
	//var path = $('body.repository').find('#fullpath');
	var path = $('#fullpath');
	if (path.size()==0) return;
	var url = Repos.getWebapp() + 'open/list/?target='+encodeURIComponent(path.text());
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
	return $('commit>author', entry).size() == 0;
}

/**
 * Adds empty placeholders for common detail entries (except name, which is probably displayed already)
 * @param e jQuery element to add to
 */
function details_addtags(e) {
 	$(e).find('div.details, span.lock').remove(); // allow refresh
	e.append('<div class="details"><span class="revision"></span><span class="datetime"></span><span class="username"></span><span class="filesize"></span></div>');
}

function details_formatSize(strBytes) {
	var b = Number(strBytes);
	if (b == Number.NaN) return strBytes;
	if (b < 1000) return strBytes + ' B';
	var f = 1.0 * b / 1024;
	if (f < 0.995) return f.toPrecision(2) + ' kB';
	if (f < 999.5) return f.toPrecision(3) + ' kB';
	f = f / 1024;
	if (f < 0.995) return f.toPrecision(2) + ' MB';
	if (f < 99.95) return f.toPrecision(3) + ' MB';
	return f.toFixed(0) + ' MB';
}

function detailsToggle() {
	$('#commandbar #showdetails').addClass('loading');
	details_repository();
}

