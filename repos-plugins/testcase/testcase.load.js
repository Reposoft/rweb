/**
 * Repos test management (c) 2008 Staffan Olsson
 */

Repos.service('index/', function() {

	var m = window.location.hash.match(/repostestcase-(R\d+)-(\d+)/);
	if (!m) return;
	var section = m[1];
	var reqnum = m[2];

	// depending on the readme plugin
	$('.contentcommands').append(
		'<a class="action" href="'+Repos.url+'edit/text/'
		+'?suggestname='+(reqnum ? reqnum : '')+'.testcase.txt'
		+'&target='+encodeURIComponent(Repos.getTarget())+'">'
		+'Add&nbsp;testcase</a>');

	$('.index a.folder').each(function() {
		$f = $(this);
		var id = $f.parent().attr('id'); 
		// only process folder matching test section
		if (!new RegExp("^"+section+"[\\s_%]").test($f.text())) return;
		// forward hash to that folder
		var href = $f.attr('href');
		$f.attr('href', href+window.location.hash);			
		// automatically go to the folder
		window.location.href = $f.attr('href');
	});
	
	// inside section folder
	var correctSection = new RegExp("/"+section+"[\\s_%]").test(Repos.getTarget());
	
	// visual
	$("a.file[href$='.testcase.txt']").each(function() {
		$t = $(this);
		$a = $('<a class="action" href="'+Repos.url+'edit/text/?target='+Repos.getTarget()+$t.attr('href')+'">run&nbsp;test</a>')
			.prepend('<img src="'+Repos.url+'style/commands/16x16/repostest.png" border="0"/>');
		if (correctSection) {
			if (new RegExp('^0*'+reqnum+"[\\D]").test($(this).text())) {
				$a.css('background-color','#ff9');
				$(this).css('background-color','#ff9');
			}
		}
		$('<li/>').append($a).appendTo($('.actions', $t.parent()));		
	});	
	
});

function reposTestcasePass() {
	return reposTestcaseReport('pass');
}
function reposTestcaseFail() {
	return reposTestcaseReport('fail');
}
function reposTestcaseReport(status) {
	var oldlog = $('#message').val();
	$('#message').val(status);
	var m = prompt('Optional: describe result of execution','');
	if (m === null) return false; // cancel
	var timestamp = new Date().toISO8601String(); // depends on repos internal dateformat code
	var text = timestamp + ' ' + status;
	if (m) text += ' (' + m + ')';
	$('#usertext').val(
		$('#usertext').val()+"\n"+text+"\n"
	);
	return true;
}

Repos.service('edit/text/', function() {
	if (Repos.isTarget('**/*.testcase.txt')) {
		$('<button type="submit">FAIL test and save</button>')
			.click(reposTestcaseFail).insertAfter('#submit');
		$('<button type="submit">PASS test and save</button>')
			.click(reposTestcasePass).insertAfter('#submit');
	}
});


