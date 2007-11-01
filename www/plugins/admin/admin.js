
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
	
	$().filter(':repos-target(/administration/repos.accs)').ready( function() {
		admin();
	});
	
	$().filter(':repos-target(/'+u+'/administration/)').ready( function() {
		user();
	});

	$().filter(':repos-target(/'+u+'/administration/repos.user)').ready( function() {
		user();
	});

})();
