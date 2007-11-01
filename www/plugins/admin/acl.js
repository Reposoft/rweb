
(function(){
	
	function c() {
	 	$('#commandbar')
			.append('<a href="../../account/create/">Create account</a>')
			.append('<a href="../../account/delete/">Delete account</a>')
			.append('<a id="reposadmin" href="../../admin/">Repos admin access</a>')
	};
	
	$().filter(':repos-target(/administration/repos.accs)').ready( function() {
		c();
	});

})();
