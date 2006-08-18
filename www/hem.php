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
	$forwardTo = basename(__FILE__);
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
<p>Login failed. You will be redirected to the login page again.</p>
</body>
</html>
<?php
}

function loginAndRedirectToHomeDir() {
	$repo = getRepositoryUrl();
	$realm = getAuthName($repo);
	doLogin($repo, $realm);
	$home = getHomeDir($repo);
	// now we can test if the login was ok (user does not have access to root)
	if (verifyLogin($home)) {
		header("Location: " . $home);
	} else {
		showLoginFailed();
	}
}

loginAndRedirectToHomeDir();

?>