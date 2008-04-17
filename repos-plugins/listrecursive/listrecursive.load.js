
Repos.service('index/', function() {
		var w = Repos.getWebapp();
		var t = encodeURI(Repos.getTarget());
		$('#commandbar').append('<a id="listrecursive" href="'+w+'open/list/?target='+t+'&recursive=1">list contents</a>');
	});
