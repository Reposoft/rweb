<?php
/*
------------  USER set? -------------
-------  yes /  -----  \ no ---------
----- =='void'?  ---  'logout' set? -
 yes /  -  \ no  -  yes /  -  \ no  -
-  OK  |  401  | wasn't in | redirect
*/

if (isset($_SERVER['PHP_AUTH_USER'])) {
	if ($_SERVER['PHP_AUTH_USER']=='void') {
		echo 'You have been logged out';
	}  else {
		if (isset($_GET['logout'])) {
			header('WWW-Authenticate: Basic realm="repos"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'Error logging out. You need to close your browser to clear authentication.';	
		} else {
			$logout_url = 'http';
			if($_SERVER['SERVER_PORT']==443) $logout_url += 's';
			$logout_url = $logout_url . "://void:LoggedOut@" . $_SERVER['SERVER_NAME'] .  ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] . '?logout=1';
			header('Location: ' . $logout_url);
		}
	}
} else {
	echo 'You were not logged in';
}
?>