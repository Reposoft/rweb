<?php
require( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__)) . "/login.inc.php" );

/**
 * Get a user's home directory of a repository
 */
function getHomeDir($repository) {
	return $repository . '/' . getReposUser() . '/trunk/';
}

function isHttps($repository) {
	return !(strpos($repository, 'https://')===false);
}

function showUserLogin() {
	$nexturl = repos_getSelfUrl() . "?login=user";
	$presentation = new Presentation();
	$presentation->assign('nexturl', $nexturl);
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/index'));
}

function showLoginCancelled() {
	$presentation = new Presentation();
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/cancel'));
}

function showLoginFailed($targetUrl) {
	//header('HTTP/1.1 401 Unauthorized');
	$nexturl = repos_getSelfRoot().'/?logout&go='.rawurlencode('?login');
	$presentation = new Presentation();
	$presentation->assign('nexturl', $nexturl);
	$presentation->assign('rooturl', repos_getSelfRoot());
	$presentation->assign('targeturl', $targetUrl);
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/failed'));
}

function loginAndRedirectToHomeDir() {
	$repo = getRepositoryUrl();
	if (isHttps($repo)) {
		enforceSSL();
	}
	if (isLoggedIn()) {
		$home = getHomeDir($repo);
		// now when we have the username we can test if the login was ok
		// (user does not have access to repository root)
		if (verifyLogin($home)) {
			login_setUsernameCookie();
			header("Location: " . $home);
		} else {
			showLoginFailed($home);
		}
	} elseif (isset($_GET['login']) && $_GET['login'] == 'user') {
		$realm = getAuthName($repo);
		if(!$realm) {
			trigger_error("Error: No login realm was found for repository $repo");
			exit;
		}
		askForCredentials($realm);
		// browser will refresh upon user input, if there is still no credentials we end up here
		showLoginCancelled();
	} else {
		showUserLogin();
	}
}

loginAndRedirectToHomeDir();

?>

