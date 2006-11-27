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
});

function details_read() {
	var e = $('body').find('div.details');
	if (e.size() > 0) {
		$.get('/repos/open/list/?target='+encodeURIComponent(e.title()), function(xml) {
				details_write(e, $('/lists/list/entry', xml)); });
	}
	// add command
	if ($('#fullpath').size() > 0) {
		$('#commandbar').append('<a id="showdetails" class="command" href="javascript:detailsToggle()">show details</a>');
	}
}

/**
 *
 * @param e jQuery element to write details to
 * @param entry the entry node from svn list --xml 
 */
function details_write(e, entry) {
	entry.each(function(){
		$('.path', e).append($('name', this).text());
		$('.username', e).append($('commit/author', this).text());
		$('.datetime', e).append($('commit/date', this).text());
		// IE can't handle this, waiting for jQuery 1.1 when bug #164 is fixed
		//$('.revision', e).append($('commit', this).attr('revision'));
		var size = $('size', this).text();
		if (size != null && size.length>0) $('.filesize', e).append(details_formatSize(size)).title(size + ' bytes');
		// if the dateformat plugin is present, do format
		$('.datetime', e).each(function() {
			if (typeof('Dateformat')!='undefined') new Dateformat().formatElement(this);
		});
	});
	e.show();
}

function details_repository() {
	//var path = $('body.repository').find('#fullpath');
	var path = $('#fullpath');
	if (path.size()==0) return;
	$.get('/repos/open/list/?target='+encodeURIComponent(path.text()), function(xml){
			$('/lists/list/entry', xml).each(function() {
				var name = $('name', this).text();
				if (this.getAttribute('kind')=='dir') name = name + '/';
				var row = new ReposFileId(name).find('row');
				if (row == null) return; // silently skip items that can't be found
				row = $(row);
				details_addtags(row);
				//$('.details',row).hide();
				details_write(row, $(this));
			});
		});
}

/**
 * Adds empty placeholders for common detail entries (except name, which is probably displayed already)
 * @param e jQuery element to add to
 */
function details_addtags(e) {
	e.append('<div class="details"><span class="revision"></span><span class="datetime"></span><span class="username"></span><span class="filesize"></span></div>');
}

function details_formatSize(strBytes) {
	var b = Number(strBytes);
	if (b == Number.NaN) return strBytes;
	if (b < 1000) return strBytes + ' B';
	var f = 1.0 * b / 1024;
	if (f < 0.995) return f.toPrecision(2) + ' kB';
	if (f < 999.5) return f.toPrecision(3) + ' kB';
	var f = f / 1024;
	if (f < 0.995) return f.toPrecision(2) + ' MB';
	if (f < 99.95) return f.toPrecision(3) + ' MB';
	return f.toFixed(0) + ' MB';
}

function detailsToggle() {
	details_repository();
	$('.details').show(); //.toggle();
}
