
(function(){

	var a = '/repos-admin/';
	var u = Repos.getUser();
	var w = Repos.getWebapp();

	function home() {
		if (!u) { // lost password should have the access control of repos web, not repos admin
			$('#commandbar').append('<a id="lostpassword" href="'+w+'admin/lostpassword/">Lost password</a>');
			// TODO extract to separate function and plug in to login pages as well
		}
		$('#commandbar').append('<a id="reposadmin" href="'+a+'">Repos Admin</a>');
	}

	function admin() {
	 	$('#commandbar')
			.append('<a href="'+w+'account/create/">Create account</a>')
			.append('<a href="'+w+'account/delete/">Delete account</a>')
			.append('<a id="reposadmin" href="'+a+'admin/">Repos Admin</a>')
	};

	function user() {
	 	$('#commandbar')
			.append('<a id="accountedit" href="'+a+'users/password/?target=/'+u+'/administration/repos.user">Edit account</a>');
	};

	function edit() {
		var e = $('a:contains(Edit in Repos)').eq(0);
		e.attr('href', e.attr('href').replace(/^file\//, a+'users/password/'));
		e.text('Edit as password file');
	};

	Repos.service('home/', home);

	Repos.target('/administration/repos.accs', function() {
		admin();
	});

	Repos.target('/'+u+'/administration/', function() {
		user();
	});

	Repos.target('/'+u+'/administration/repos.user', function() {
		if (location.href.indexOf(a)>=0) return; // already in admin
		user();
	});

	Repos.service('edit/', edit);

})();
