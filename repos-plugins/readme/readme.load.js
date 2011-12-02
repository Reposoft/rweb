
// event declarations
$(document).bind('repos-readme-loaded', function(ev, container) {});

Repos.service('index/', function() {
	// check file list for one named acconrding to convetion
	var readme = $('#open\\:repos\\.txt')[0];
	// display contents if it exists
	if (typeof readme != 'undefined') {
		$.get(readme.href.replace(/(\?|&)rweb=\w+/, ''), function(data) {
			$('body').say({tag:'p',id:"repos-readme",
				text:data.replace(/\r?\n/g,'<br />'),
				title:'Contents of repos.txt'
				});
			$(document).trigger('repos-readme-loaded', [this]);
		});
		return;
	}
	// OR readme as XHTML
	readme = $('#open\\:repos\\.html')[0];
	if (typeof readme != 'undefined') {
		$('body').say({id:'repos-readme'});
		$('#repos-readme').load(readme.href.replace(/(\?|&)rweb=\w+/, '$1'), function() {
			$(document).trigger('repos-readme-loaded', [this]);
		});
		return;
	}
	// else, display button to add readme if it does not exist
	var w = Repos.getWebapp();
	var t = encodeURI(Repos.getTarget());
	var a = $('<a/>').attr('id','repos-edit').addClass('action')
		.attr('href', w+'edit/text/?target='+t+'&base='+Repos.getBase()+'&suggestname=repos.txt').html('add&nbsp;text');
	// currently no other plugin uses contentcommands
	var e = $('<div/>').addClass('contentcommands').append(a).insertAfter('#path');
} );
