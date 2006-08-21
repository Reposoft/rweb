<?php
/**
 * Redirects to server start page
 *
 * if ?logout is set the script clears HTTP auth credentials before logout
 * (but currently it does not know what realm it is logging out from)
 *
 * This script is intended to be placed in server root,
 * for logout to be effective with all URLs on the server.
 */

// HTTP/1.1 disable caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0 disable caching
header("Pragma: no-cache");

// ------- hard coded settings -------
// Server startpage, browser will be redirected here when logout is done / not needed
var $startpage = './repos/';
// Realm to send to verify login befor logout
// maybe this should be retreived from session or from repos.properties
var $realm = 'Optime';

// first the logout procedure on ?logout=
if (isset($_GET['logout'])) {
	if ($_GET['logout']=='verify') {
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			if ($_SERVER['PHP_AUTH_USER'] == 'void') {
				doLogoutVoid();
			} else {
				showCouldNotLogOutPage();
			}
		} else {
			showStartPage();
		}
	} else {
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			doLogout();
		} else {
			requireAuth();
		}
	}
	
	
} else {
	showStartPage();
}
exit;
// go to home page
function showStartPage() {
	header('Location: '.);
}

function doLogout() {
	header('HTTP/1.0 401 Unauthorized');
	showLoggingOutPage();
}

function doLogoutVoid() {
	header('HTTP/1.0 401 Unauthorized');
	showStartPage();
}

function requireAuth() {
	header('WWW-Authenticate: Basic realm="' . $realm . '"');
	header('HTTP/1.1 401 Authorization Required');
}

// --- logout pages ---

function showLoggingOutPage() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="refresh" content="3;url=?logout=verify">
<title>Logging out of repos.se</title>
</head>
<p>Logging out of repos.se<p>
<p><small>You should be automatically redirected to the <a href="?logout=verify">startpage</a>.</small></p>
<body>
</body>
</html>
<?php
}

function showLoggedOutPage() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Logged out of repos.se</title>
</head>

<body>
</body>
</html>
<?php 
}

function showCouldNotLogOutPage() {
	if (strstr ($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>browser specific logout of repos.se</title>
</head>
<script language="javascript">
document.execCommand('ClearAuthenticationCache') //clear cache
parent.location.href="../../" //redirect after logged out
</script>
<body>
<p>Clearing your browser's credentials for repos.se.</p>
</body>
</html>
<?php
	} else {
		$logout_url = 'http';
		if($_SERVER['SERVER_PORT']==443) $logout_url = $logout_url.'s';
		$logout_url = $logout_url . "://void:LoggedOut@" . $_SERVER['SERVER_NAME'] .  ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] . '?logout=verify';
		// note that redirect including a password is illegal in IE
		header("Location: $logout_url");
	}
}
