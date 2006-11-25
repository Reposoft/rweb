

$(document).ready(function() {
	details_read();
});


function details_read() {
	var e = $('body').find('div.details');
	if (e.size()==0) {
		alert('no details requested');
	}
	$.ajax({type:'GET', 
		url:'/repos/open/list/?target='+e.title(), 
		dataType:'xml',
		success:function(xml){ details_process(e, xml); }});
}

function details_process(e, xml) {
	$('/lists/list/entry', xml).each(function(){
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
