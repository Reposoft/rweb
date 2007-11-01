
(function(){
	
	function c() {
	 	$('#commandbar')
			.append('<a href="../../account/create/">Create account</a>')
			.append('<a href="../../account/delete/">Delete account</a>')
			.append('<a id="reposadmin" href="../../admin/">Repos admin access</a>')
	};
	
	console.log($().filter(':repos-target(/administration/repos.accs)'));
	console.log('default',$());
	$([]).ready(function() { console.warn('ready executed for empty bucket'); });
	$().filter(':repos-target(/administration/repos.accs)').ready( function() {
		c();
	});

})();
