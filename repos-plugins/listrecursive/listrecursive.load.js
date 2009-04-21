
Repos.service('index/', function() {
		var w = Repos.getWebapp();
		var t = encodeURI(Repos.getTarget());
		$('#commandbar').append('<a id="listrecursive" href="'+w+'open/list/?target='+t+'&recursive=1">list&nbsp;contents</a>');
		// explicit support for base parameter
		// (generally in Repos it should be treated in an AOP fashion but it is not solved for this case)
		var base = $('#base').text();
		if (base) $('#listrecursive').attr('href', $('#listrecursive').attr('href') + '&base=' + base);
	});
