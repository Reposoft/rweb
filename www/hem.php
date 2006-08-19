<?php
require( dirname(__FILE__) . "/conf/repos.properties.php" ); // default repository
require( dirname(__FILE__) . "/login.inc.php" );

/**
 * Get a user's home directory of a repository
 */
function getHomeDir($repository) {
	return $repository . '/' . getReposUser() . '/trunk/';
}

function showLoginFailed($targetUrl) {
	header('HTTP/1.0 401 Unauthorized');
	$forwardTo = "hem.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Repos login failed</title>
<?php
// <meta http-equiv="refresh" content="5;url='.$forwardTo.'">
?>
</head>
<body>
<p>Login failed</p>
<p>Could not access <?php echo($targetUrl) ?>. Invalid username or password.</p>
<p><strong><a accesskey="r" href="<?php echo($forwardTo); ?>">retry</a></strong></p>
<p>If the login box does not whow up when you click 'retry', you browser may have cached the invalid login. Do <a href="../logout/">logout</a> and try again. 
<p><a href="./">Return to startpage</a></p>
</body>
</html>
<?php
}

function loginAndRedirectToHomeDir() {
	$repo = getRepositoryUrl();
	if (isLoggedIn()) {
		$home = getHomeDir($repo);
		// now we can test if the login was ok (user does not have access to root)
		if (verifyLogin($home)) {
			header("Location: " . $home);
		} else {
			showLoginFailed($home);
		}
	} else {
		$realm = getAuthName($repo);
		if(!$realm) {
			echo("Error: No login realm was found for repository $repo");
			exit;
		}
		askForCredentials($realm);
	}
}

loginAndRedirectToHomeDir();

?>