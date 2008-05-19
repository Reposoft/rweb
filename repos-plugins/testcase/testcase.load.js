/**
 * Repos test management (c) 2008 Staffan Olsson
 */

reposTestcase = function() {
	$t = $(this);
	$t.prepend('<img src="'+Repos.webapp+'style/commands/16x16/repostest.png" border="0"/>');
	//$t.css('background-image', Repos.webapp+'style/commands/16x16/repostest.png');
};

Repos.service('index/', function() {

	var m = window.location.hash.match(/repostestcase-(R\d+)-(\d+)/);
	if (!m) return;

	// visual
	$("a[href$='.testcase.txt']").each(reposTestcase);

	// depending on the readme plugin
	$('.contentcommands').append(
		'<a class="action" href="'+Repos.webapp+'edit/file/'
		+'?suggestname='+''+'.testcase.txt'
		+'&target='+encodeURIComponent(Repos.target)+'">'
		+'Add&nbsp;testcase</a>');

	var section = m[1];
	var reqnum = m[2];
	
	$('.index a.folder').each(function() {
		$f = $(this);
		var f = $f.text();
		var id = $f.parent().attr('id'); 
		// only process folder matching test section
		if (!(new RegExp("^"+section+"[\\s_]")).test(f)) return;
		// forward hash to that folder
		var href = $f.attr('href');
		$f.attr('href', href+'#'+window.location.hash;				
		// try to list contents
		$.ajax({
			dataType: 'script',
			url: Repos.webapp+'open/json/?selector='+id+'&target='+encodeURI(href),
			success: function() {	
				alert('rjo');
			},
			error: function(req, textStatus, errorThrown) {
				alert(errorThrown);
			}
		});
	});
	
});

Repos.service('edit/text/', function() {
	if (/\.testcase\.txt$/.test(Repos.target)) {
		alert('pass/fail');
	}
});
 
