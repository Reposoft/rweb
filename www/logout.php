<?php
/**
 * Logging out of BASIC authentication
 */
function showLogoutScreen() {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="REFRESH" content="0; URL=http://www.repos.se/">
<title>repos.se</title>
<link href="../themes/simple/css/repos-standard.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="760" border="0" align="center" class="info">
  <tr>
	<th class="info" width="25%">Logout</th>
	<td class="info" colspan="3">You have been logged out (hopefully, but this is still beta)</td>
  </tr>
</table>
</body>
</html>
<?php
	exit;
}
/*
if (!isset($_SERVER['PHP_AUTH_USER'])) {
	showLogoutScreen("No user credentials found");
}
*/

// *** IE6 SP1+ ****
	// seems not to work. Maybe a javascript is needed to do a redirect like the one below
	// Or when calling logout page from somewhere include the user and password already
if (strstr ($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
	header('HTTP/1.0 401 Unauthorized');
	showLogoutScreen("IE6 sent 401 headers");
}

// *** other browsers ***
if ($_SERVER['PHP_AUTH_USER']=='void') {
	showLogoutScreen("Basic auth username was set to 'void'");
}
require_once('login.inc.php');
if (isset($_GET['logout'])) {
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
?>