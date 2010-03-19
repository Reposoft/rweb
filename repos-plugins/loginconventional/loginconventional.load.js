/**
 * Uses the standard Repos Web login/logout functionality that assumes
 * that all users have read access eiter to repository root
 * (originally not parent path) or a folder named as the username
 * in repository root (the user's home folder).
 */

(function() {
	
	var loginUrl = '/?login';
	var logoutUrl = '/?logout';
	var startpageUrl = 'open/start/'; // relative to repos-web
	
	var addLogin = function() {
		var a = $('<a id="login"/>').attr('href', loginUrl).text('login');
		$('#commandbar').prepend(a);
		a.focus();
	};
	
	/**
	 * Place to the right in commandbar, with fallback to default placement in vertical commandbar.
	 * @param {jQuery|Element} e The eleement to add
	 */
	var placeRight = function(e) {
		if (!$('#commandbar .right').prepend(e).size()) {
			$('#commandbar').append(e);
		}
	};
	
	var addLogout = function() {
		if ($.browser.safari) {
			// logout logic does not work in safari so the button would just mislead users to believe they logged out
			return;
		}
		var logout = $('<a id="logout"/>').attr('href', logoutUrl).text('logout');
		placeRight(logout);
	};
	
	Repos.service('home/', function() {
		$('#loginstatus').css('display', 'block');
		if (Repos.getUser()) {
			addLogout();		
		} else {
			addLogin();
			$('#start').hide();
		}
	});
	
	Repos.service('index/', function() {
		if (Repos.getUser()) {
			addLogout();			
		} else {
			placeRight($('<span id="logout" title="Not logged in through Repos">logout</span>'));
		}
	});
	
})();
