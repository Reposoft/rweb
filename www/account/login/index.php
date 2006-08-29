<?php
require( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__)) . "/login.inc.php" );

/**
 * Get a user's home directory of a repository
 */
function getHomeDir($repository) {
	return $repository . '/' . getReposUser() . '/trunk/';
}

function showUserLogin() {
	$nexturl = SELF_URL . "?user";
	$presentation = new Presentation();
	$presentation->assign('nexturl', $nexturl);
	$presentation->display(getLocaleFile(dirname(__FILE__).'/index'));
}

function showLoginCancelled() {
	$presentation = new Presentation();
	$presentation->display(getLocaleFile(dirname(__FILE__).'/cancel'));
}

function showLoginFailed($targetUrl) {
	//header('HTTP/1.1 401 Unauthorized');
	$nexturl = SELF_ROOT.'/?logout&go='.rawurlencode('?login');
	$presentation = new Presentation();
	$presentation->assign('nexturl', $nexturl);
	$presentation->assign('rooturl', SELF_ROOT);
	$presentation->assign('targeturl', $targetUrl);
	$presentation->display(getLocaleFile(dirname(__FILE__).'/failed'));
}

function loginAndRedirectToHomeDir() {
	enforceSSL();
	$repo = getRepositoryUrl();
	if (isLoggedIn()) {
		$home = getHomeDir($repo);
		// now when we have the username we can test if the login was ok
		// (user does not have access to repository root)
		if (verifyLogin($home)) {
			header("Location: " . $home);
		} else {
			showLoginFailed($home);
		}
	} elseif (isset($_GET['user'])) {
		$realm = getAuthName($repo);
		if(!$realm) {
			trigger_error("Error: No login realm was found for repository $repo");
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

