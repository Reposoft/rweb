

$(document).ready(function() {
	details_read();
});

function details_read() {
	var e = $('body').find('div.details');
	if (e.size() == 0) {
		details_repository();
		return;
	}
	$.ajax({type:'GET', 
		url:'/repos/open/list/?target='+e.title(), 
		dataType:'xml',
		success:function(xml){ details_write(e, $('/lists/list/entry', xml)); }});
	details_repository();
}

/**
 *
 * @param e jQuery element to write details to
 * @param entry the entry node from svn list --xml 
 */
function details_write(e, entry) {
	entry.each(function(){
		$('.path', e).append($('name', this).text());
		$('.size', e).append($('size', this).text());
		$('.revision', e).append($('commit', this).attr('revision'));
		$('.username', e).append($('commit/author', this).text());
		$('.datetime', e).append($('commit/date', this).text());
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
	console.log('request details for ' + path.text());
	$.ajax({type:'GET', 
		url:'/repos/open/list/?target=' + path.text(), 
		dataType:'xml',
		success:function(xml){
			$('/lists/list/entry', xml).each(function() {
				var name = $('name', this).text();
				if (this.getAttribute('kind')=='dir') name = name + '/';
				console.log('show details for ' + name);
				var row = $(new ReposFileId(name).find('row'));
				details_addtags(row);
				$('.details',row).hide();
				details_write(row, $(this));
			});
			// add command
			$('#commandbar').append('<a class="command" href="javascript:detailsToggle()">show details</a>');
		}});
}

/**
 * Adds empty placeholders for common detail entries
 * @param e jQuery element to add to
 */
function details_addtags(e) {
	e.append('<div class="details"><span class="revision"></span><span class="datetime"></span><span class="username"></span><span class="size"></span></div>');
}

function detailsToggle() {
	$('.details').show(); //.toggle();
}

