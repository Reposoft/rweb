
(function(){
	
	function check() {
		if (Repos.getUser()) return;
		var o = $('#logout');
		o.replaceWith('<span id="logout" title="Not logged in through Repos">'+o.html()+'</span>');
	}
	
	$().ready(check);
	
})();
