
Repos.service('index/', function() {
	var readme = $('a.file[href=repos.txt]')[0];
	if (typeof readme != 'undefined') {
		$.get(readme.href, function(data) {
			$('body').say({tag:'p',id:"repos-readme",
				text:data.replace(/\r?\n/g,'<br />'),
				title:'Contents of repos.txt'
				});
		});
	} else {
		var add = $('#repos-edit');
		add.attr('href', add.attr('href')+'&suggestname=repos.txt');
	}
} );
