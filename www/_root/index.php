<?php
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

// go to home page
showStartPage() {
	header('Location: ./');
}

doLogout() {
	header('HTTP/1.0 401 Unauthorized');
	showLoggingOutPage();
}

doLogoutVoid() {
	header('HTTP/1.0 401 Unauthorized');
	showStartPage();
}

requireAuth() {
	$realm = 'Optime';
	header('WWW-Authenticate: Basic realm="' . $realm . '"');
	header('HTTP/1.0 401 Unauthorized');
}

// --- logout pages ---

showLoggingOutPage() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="refresh" content="3;url=?logout=verify">
<title>Logging out of repos.se</title>
</head>
<p>Logging out of repos.se<p>
<p><small>You should be automatically redirected to the <a href="?logout=verify">verify</a> page</small></p>
<body>
</body>
</html>
<?php
}

showLoggedOutPage() {
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

showCouldNotLogOutPage() {
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
		var $logout_url = 'http';
		if($_SERVER['SERVER_PORT']==443) $logout_url = $logout_url.'s';
		$logout_url = $logout_url . "://void:LoggedOut@" . $_SERVER['SERVER_NAME'] .  ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] . '?logout=verify';
		// note that redirect including a password is illegal in IE
		header("Location: $logout_url");
	}
}
