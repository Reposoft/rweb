
(function(){
	
	var u = Repos.getUser();
	var w = Repos.getWebapp();
	
	function admin() {
	 	$('#commandbar')
			.append('<a href="'+w+'account/create/">Create account</a>')
			.append('<a href="'+w+'account/delete/">Delete account</a>')
			.append('<a id="reposadmin" href="'+w+'admin/">Repos admin access</a>')
	};
	
	function user() {
	 	$('#commandbar')
			.append('<a id="accountedit" href="'+w+'edit/file/?target=/'+u+'/administration/repos.user">Edit account</a>');
	};
	
	Repos.target('/administration/repos.accs', function() {
		admin();
	});
	
	Repos.target('/'+u+'/administration/', function() {
		user();
	});

	Repos.target('/'+u+'/administration/repos.user', function() {
		user();
	});

})();
