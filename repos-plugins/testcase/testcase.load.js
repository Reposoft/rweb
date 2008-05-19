/**
 * Repos test management (c) 2008 Staffan Olsson
 */

reposTestcase = function() {
	$t = $(this);
	$a = $('<a class="action" href="'+Repos.url+'edit/text/?target='+Repos.getTarget()+$t.attr('href')+'">run&nbsp;test</a>')
		.prepend('<img src="'+Repos.url+'style/commands/16x16/repostest.png" border="0"/>');
	$('<li/>').append($a).appendTo($('.actions', $t.parent()));
	//$t.css('background-image', Repos.url+'style/commands/16x16/repostest.png');
};

Repos.service('index/', function() {

	var m = window.location.hash.match(/repostestcase-(R\d+)-(\d+)/);
	if (!m) return;

	// visual
	$("a.file[href$='.testcase.txt']").each(reposTestcase);

	// depending on the readme plugin
	$('.contentcommands').append(
		'<a class="action" href="'+Repos.url+'edit/text/'
		+'?suggestname='+''+'.testcase.txt'
		+'&target='+encodeURIComponent(Repos.getTarget())+'">'
		+'Add&nbsp;testcase</a>');

	var section = m[1];
	var reqnum = m[2];
	$('.index a.folder').each(function() {
		$f = $(this);
		var id = $f.parent().attr('id'); 
		// only process folder matching test section
		if (!(new RegExp("^"+section+"[\\s_%]")).test($f.text())) return;
		// forward hash to that folder
		var href = $f.attr('href');
		$f.attr('href', href+window.location.hash);			
		// try to list contents
		//alert(Repos.url+'open/json/?selector='+id+'&target='+Repos.getTarget()+href);
		$.ajax({
			dataType: 'script',
			url: Repos.url+'open/json/?selector='+id+'&target='+Repos.getTarget()+href,
			success: function() {
			},
			error: function(req, textStatus, errorThrown) {
				alert('testcase script error');
			}
		});
	});
	
});

Repos.service('edit/text/', function() {
	if (/\.testcase\.txt$/.test(Repos.target)) {
		alert('pass/fail');
	}
});
