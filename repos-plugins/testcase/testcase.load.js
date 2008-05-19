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

	if (correctSection) {
		$('body').say({tag:'p',id:"repos-readme",
				text:'Highlighting the test cases that match the selected requirement id <strong>'+section+'-'+reqnum+'</strong>, if any. ',
				title:'Repos testcases'
				});
	}	

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

var reposTestcaseOriginalContents;

function reposTestcasePass() {
	return reposTestcaseReport('pass');
}
function reposTestcaseFail() {
	return reposTestcaseReport('fail');
}
function reposTestcaseReport(status) {
	var oldlog = $('#message').val();
	var m = prompt('Optional: describe result of execution','');
	if (m === null) return false; // cancel
	
	if (status!='pass' && status !='fail') {
		alert('invalid test result: '+status);
	}
	var propertyname = "repos:testresult";
	$.ajax({type: "POST",
		url: Repos.url+"edit/propset/",
		data: {name: propertyname,
			value: status,
			message: status + (m ? ' - '+m : '')
		},
		success: function(msg) {
			$('body').say("Test result saved as svn property repos:testresult");
			if ($('#usertext').val() != reposTestcaseOriginalContents) {
				$('body').say("Testcase contents has changed. You should Save the form.");
			}
		},
		error: function() {
			alert('An error occured. Test result could not be saved. It may still be possible to submit the form.');
		}
	});
	return false;

	/* old save strategy
	var timestamp = new Date().toISO8601String(); // depends on repos internal dateformat code
	var text = timestamp + ' ' + status;
	if (m) text += ' (' + m + ')';

	$('#message').val(status);
	$('#usertext').val(
		$('#usertext').val()+"\n"+text+"\n"
	);
	return true;
	*/
}

Repos.service('edit/text/', function() {
	
	if (!Repos.isTarget('**/*.testcase.txt')) return;

	$('<a id="lostpassword"/>').addClass('command').html('issu.se')
		.attr('id','repostest') // to get the icon
		.attr('target','_blank')
		.attr('href','http://www.issu.se/jtrac/app/login')
		.appendTo('#commandbar');

	reposTestcaseOriginalContents = $('#usertext').val();

	$('<button type="submit">FAIL test and save</button>')
		.click(reposTestcaseFail).insertAfter('#submit')
		.css('background-color','#ffddbb'); // same as in repos unit tests
	$('<button type="submit">PASS test and save</button>')
		.click(reposTestcasePass).insertAfter('#submit')
		.css('background-color','#ddffbb'); // same as in repos unit tests

});


