
(function(){
	
	function check() {
		var o = $('#logout');
		if (jQuery.browser.safari) {
			// logout logic does not work in safari so the button would just mislead users to believe they logged out
			o.remove();
		}
		if (Repos.getUser()) return;
		o.replaceWith('<span id="logout" title="Not logged in through Repos">'+o.html()+'</span>');
	}
	
	$().ready(check);
	
})();
