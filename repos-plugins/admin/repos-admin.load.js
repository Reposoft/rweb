
(function(){

	var a = '/repos-admin/';
	var u = Repos.getUser();

	function admin() {
		$('#commandbar')
			.append('<a id="reposadmin" href="'+a+'">Repos&nbsp;Admin</a>');
	}

	function useradmin() {
	 	$('#commandbar')
			.append('<a id="accountedit" href="'+a+'account/create/">Create&nbsp;account</a>') //using accountedit as id to get the same icon
			.append('<a href="'+a+'account/delete/">Delete&nbsp;account</a>');
		admin();
	};

	function user() {
	 	$('#commandbar')
			.append('<a id="accountedit" href="'+a+'account/password/?target=/'+u+'/administration/repos.user">Edit&nbsp;account</a>');
	};

	function edit() {
		var e = $('a:contains(Edit in Repos)').eq(0);
		e.attr('href', e.attr('href').replace(/^file\//, a+'account/password/'));
		e.text('Edit as password file');
	};

	Repos.target('/administration/', admin);

	Repos.target('/administration/repos.accs', useradmin);

	Repos.target('/'+u+'/administration/', function() {
		Repos.service('index/', user);
	});

	Repos.target('/'+u+'/administration/repos.user', function() {
		if (location.href.indexOf(a)>=0) return; // already in admin
		user();
		Repos.service('edit/', edit);
	});

})();
