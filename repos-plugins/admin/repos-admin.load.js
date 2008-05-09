
(function(){

	var a = '/repos-admin/';
	var u = Repos.getUser();

	function home() {
		if (!u) { // lost password should have the access control of repos web, not repos admin
			$('#commandbar').append('<a id="lostpassword" href="'+a+'account/reset/">Lost password</a>');
			// TODO extract to separate function and plug in to login pages as well
		}
		// not wanted for most users // $('#commandbar').append('<a id="reposadmin" href="'+a+'">Repos Admin</a>');
	}

	function admin() {
	 	$('#commandbar')
			.append('<a href="'+a+'account/create/">Create account</a>')
			.append('<a href="'+a+'account/delete/">Delete account</a>')
			.append('<a id="reposadmin" href="'+a+'admin/">Repos Admin</a>')
	};

	function user() {
	 	$('#commandbar')
			.append('<a id="accountedit" href="'+a+'account/password/?target=/'+u+'/administration/repos.user">Edit account</a>');
	};

	function edit() {
		var e = $('a:contains(Edit in Repos)').eq(0);
		e.attr('href', e.attr('href').replace(/^file\//, a+'account/password/'));
		e.text('Edit as password file');
	};

	Repos.service('home/', home);

	Repos.target('/administration/repos.accs', admin);

	Repos.target('/'+u+'/administration/', function() {
		Repos.service('index/', user);
	});

	Repos.target('/'+u+'/administration/repos.user', function() {
		if (location.href.indexOf(a)>=0) return; // already in admin
		user();
		Repos.service('edit/', edit);
	});

})();
