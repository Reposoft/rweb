<?php
/**
 * This is currently only a test script to try logging out of BASIC auth.
 */

if (strstr ($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
	// seems t not work. Maybe a javascript is needed to do a redirect like the one below
	// Or when calling logout page from somewhere include the user and password already
	header('HTTP/1.0 401 Unauthorized');
	echo 'Your IE have been logged out';
	exit;
}
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
			// note that redirect including a password is illegal in IE
			header("Location: $logout_url");
		}
	}
} else {
	echo 'You were not logged in';
}
?>