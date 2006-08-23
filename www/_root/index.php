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

// show a result using redirect-after-post from conf/Presentation.class.php
if(isset($_GET['result'])) {

	require(dirname(__FILE__) . '/repos/conf/repos.properties.php');
	
	$resultFile = getTempDir('pages') . $_GET['result'];
	$handle = fopen($resultFile, "r");
	fpassthru($handle);
	fclose($handle);
	exit;
}

// HTTP/1.1 disable caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0 disable caching
header("Pragma: no-cache");

// ------- hard coded settings -------
// Server startpage, browser will be redirected here when logout is done / not needed
function getAfterLogoutPage() {
	if (isset($_GET['go'])) {
		return $_GET['go'];
	}
	return 'http://www.repos.se/repos/';
}
function getAfterLogoutPageAbsolute() {
	$next = getAfterLogoutPage();
	if (strpos($next,'://')>1) {
		return $next;
	}
	$url = 'http';
	if ($_SERVER['SERVER_PORT']==443) $url .= 's';
	$url .= '://' . $_SERVER['SERVER_NAME'];
	$url .= $next; // assuming $next starts with a '/'
	return $url;
}
// Realm to send to verify login befor logout
// maybe this should be retreived from session or from repos.properties
function getRealm() {
	return 'Optime';
}

function getVerifyPage() {
	$url = '?logout=verify';
	if (isset($_GET['go'])) {
		$url .= '&go='.$_GET['go'];
	}
	return $url;
}

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
			showAfterLogoutPage();
		}
	} else {
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			doLogout();
		} else {
			// could be that the browser does not accept the logout if it has not sent credentials, or maybe this does nothing good and prevents reload
			requireAuth();
		}
	}
} else {
	showAfterLogoutPage();
}

function doLogout() {
	header('HTTP/1.1 401 Unauthorized');
	showLoggingOutPage();
}

function doLogoutVoid() {
	header('HTTP/1.1 401 Unauthorized');
	showAfterLogoutPage();
}

function requireAuth() {
	header('WWW-Authenticate: Basic realm="' . getRealm() . '"');
	header('HTTP/1.1 401 Authorization Required');
}

// --- logout pages ---

function showAfterLogoutPage() {
	$next = getAfterLogoutPageAbsolute();
	header("Location: $next");
}

function showLoggingOutPage() {
	$next = getVerifyPage();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="refresh" content="0;url=<?php echo($next); ?>">
<title>Logging out of repos.se ...</title>
</head>
<p>Logging out of repos.se ...<p>
<p><small>If your browser does not redirect automatically, click <a href="<?php echo($next); ?>">here</a>.</small></p>
<body>
</body>
</html>
<?php
}

function showCouldNotLogOutPage() {
	if (strstr ($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
		// redirect including a password is illegal in IE
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>browser specific logout for repos.se</title>
</head>
<script language="javascript">
document.execCommand('ClearAuthenticationCache') //clear cache
parent.location.href="<?php echo(getAfterLogoutPage()); ?>" //redirect after logged out
</script>
<body>
<p>Clearing your browser's credentials for repos.se.</p>
</body>
</html>
<?php
	} else {
		// redirecting to the exact same url with user 'void', expecting the next 401 header to make browser enough confused to clear auth cache
		$logout_url = 'http';
		if($_SERVER['SERVER_PORT']==443) $logout_url .= 's';
		$logout_url .= "://void:LoggedOut@" . $_SERVER['SERVER_NAME'] . '/' . getVerifyPage();
		header("Location: $logout_url");
	}
}
?>
