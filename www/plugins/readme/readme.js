
Repos.service('index/', function() {
	$('a.file[href=repos.txt]').each( function() {
		$.get(this.href, function(data) {
			$('body').say({tag:'p',id:"repos-readme",
				text:data.replace(/\r?\n/g,'<br />'),
				title:'Contents of repos.txt'
				});
		});
	});
} );
