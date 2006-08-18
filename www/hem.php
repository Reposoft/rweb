<?php
require( dirname(__FILE__) . "/conf/repos.properties.php" ); // default repository
require( dirname(__FILE__) . "/login.inc.php" );

/**
 * Get a user's home directory of a repository
 */
function getHomeDir($repository) {
	return $repository . '/' . getReposUser() . '/trunk/';
}

function showLoginFailed() {
	$forwardTo = "logout.php"; // Going to the login page again will create an endless loop
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Repos login failed</title>
<?php
echo '<meta http-equiv="refresh" content="3;url='.$forwardTo.'">';
?>
</head>
<body>
<p>Login failed. You will be redirected to the start page again.</p>
</body>
</html>
<?php
}

function loginAndRedirectToHomeDir() {
	$repo = getRepositoryUrl();
	if (isLoggedIn()) {
		$home = getHomeDir($repo);
		// now we can test if the login was ok (user does not have access to root)
		echo("Home: $home, User: ".getReposUser());
		exit;
		if (verifyLogin($home)) {
			header("Location: " . $home);
		} else {
			showLoginFailed();
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