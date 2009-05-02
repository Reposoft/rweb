
Repos.service('index/', function() {
	// check file list for one named acconrding to convetion
	var readme = $('a.file[href=repos.txt]')[0];
	// display contents if it exists
	if (typeof readme != 'undefined') {
		$.get(readme.href, function(data) {
			$('body').say({tag:'p',id:"repos-readme",
				text:data.replace(/\r?\n/g,'<br />'),
				title:'Contents of repos.txt'
				});
		});
		return;
	}
	// OR readme as XHTML
	readme = $('a.file[href=repos.html]')[0];
	if (typeof readme != 'undefined') {
		$('body').say({id:'repos-readme'});
		$('#repos-readme').load(readme.href);
		return;
	}
	// else, display button to add readme if it does not exist
	var w = Repos.getWebapp();
	var t = encodeURI(Repos.getTarget());
	var a = $('<a/>').attr('id','repos-edit').addClass('action')
		.attr('href', w+'edit/text/?target='+t+'&suggestname=repos.txt').html('add&nbsp;text');
	// currently no other plugin uses contentcommands
	$('<div/>').addClass('contentcommands').append(a).insertAfter('#path');
} );
