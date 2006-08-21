<?php
/**
 * Logging out of BASIC authentication
 *
 * This is quite tricky. Would be easier to keep server side state and return 401 header, but that's difficult for the repository browsing.
 */
function showLogoutScreen($message) {
	// if username is 'void', is there some way to get rid of it?
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="REFRESH" content="3; URL=../../">
<title>repos.se</title>
<link href="../../themes/simple/css/repos-standard.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="760" border="0" align="center" class="info">
  <tr>
	<th class="info" width="25%">Logout</th>
	<td class="info" colspan="3">
		<p>You have been logged out of repos.</p>
		<p><?php echo $message ?></p>
		<p>You will be redirected to the <a href="../../">startpage</a> in three seconds</p>
	</td>
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
if (strstr ($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Cont againent-Type" content="text/html; charset=utf-8">
<title>repos.se</title>
<link href="../../themes/simple/css/repos-standard.css" rel="stylesheet" type="text/css">
</head>
<script language="javascript">
document.execCommand('ClearAuthenticationCache') //clear cache
parent.location.href="../../" //redirect after logged out
</script>
<body>
<table width="760" border="0" align="center" class="info">
  <tr>
	<th class="info" width="25%">Logout</th>
	<td class="info" colspan="3">You are now logged out using javascript for Internet Explorer 6 SP1+.<br />Redirecting to <a href="../../">startpage</a>.</td>
  </tr>
</table>
</body>
</html>
<?php
	
} else {
// *** other browsers ***
// first need to check the credentials for this realm
// a successful logout using forced username and redirect would get us here
if ($_SERVER['PHP_AUTH_USER']=='void') {
	showLogoutScreen("Basic auth username was set to 'void'");
}
// an unsuccessful logout would end up here, if the logout attemt sets the 'logout' paramter
if (isset($_GET['logout'])) {
	header('HTTP/1.0 401 Unauthorized');
	showLogoutScreen("Did an attempt to log you out, but your browser may not support it.");
}
// try to log out using redirect
$logout_url = 'http';
if($_SERVER['SERVER_PORT']==443) $logout_url = $logout_url.'s';
$logout_url = $logout_url . "://void:LoggedOut@" . $_SERVER['SERVER_NAME'] .  ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] . '?logout=1';
// note that redirect including a password is illegal in IE
header("Location: $logout_url");
}
?>