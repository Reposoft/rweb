<?php
function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require( upOne(upOne(dirname(__FILE__))) . "/conf/repos.properties.php" ); // used by login.inc.php to get repository name
require( upOne(dirname(__FILE__)) . "/login.inc.php" );
/**
 * Get a user's home directory of a repository
 */
function getHomeDir($repository) {
	return $repository . '/' . getReposUser() . '/trunk/';
}

function showUserLogin() {
	$forwardTo = "?user";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Login to repos.se</title>
<meta http-equiv="refresh" content="0;url=<?php echo($forwardTo); ?>" />
</head>
<body>
<p>Enter your username and password in the <a href="<?php echo($forwardTo); ?>">login box</a> that should have popped up now.</p>
<p><small>Before you click OK, please verify that this is a secure connection (the address should start with "http<strong>s</strong>://").</small></p>
</body>
</html>
<?php
}

function showLoginCancelled() {
	echo("<html><body>Login cancelled. Return to the <a href=\"../../\">startpage</a>.</body></html>");
}

function showLoginFailed($targetUrl) {
	//header('HTTP/1.1 401 Unauthorized');
	$forwardTo = "/?logout&go=".str_replace("?user", "", $_SERVER['REQUEST_URI']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Repos login failed</title>
<meta http-equiv="refresh" content="5;url=<?php echo($forwardTo); ?>" />
</head>
<body>
<p>Login failed. The <a href="<?php echo($forwardTo); ?>">try again</a> box will appear automatically</p>
<p><small>Could not access <?php echo($targetUrl) ?>. Invalid username or password. <a href="../../">Return to startpage</a></small></p>
</body>
</html>
<?php
}

function loginAndRedirectToHomeDir() {
	enforceSSL();
	$repo = getRepositoryUrl();
	if (isLoggedIn()) {
		$home = getHomeDir($repo);
		// now we can test if the login was ok (user does not have access to root)
		if (verifyLogin($home)) {
			header("Location: " . $home);
		} else {
			showLoginFailed($home);
		}
	} elseif (isset($_GET['user'])) {
		$realm = getAuthName($repo);
		if(!$realm) {
			echo("Error: No login realm was found for repository $repo");
			exit;
		}
		askForCredentials($realm);
		// browser will refresh upon user input
		showLoginCancelled();
	} else {
		showUserLogin();
	}
}

loginAndRedirectToHomeDir();

?>

