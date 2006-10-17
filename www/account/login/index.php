<?php
require( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__)) . "/login.inc.php" );

/**
 * Get a user's home directory of a repository, allow override with 'go' parameter as in logout
 * @param String repository, with tailing slash
 */
function getHomeDir($repository) {
	$home = $repository . login_encodeUsernameForURL(getReposUser()) . '/trunk/';
	$exist = login_getFirstNon404Parent($home);
	if (!$exist) trigger_error("Could not find a valid URL or parent URL for $home", E_USER_ERROR);
	return $exist;
}

function login_encodeUsernameForURL($username) {
	$username = str_replace(' ', '%20', $username);
	return $username;
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
	$presentation->assign('start', getParent(getWebapp())); // not the same host if the repository is ssl
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/cancel'));
}

function showLoginFailed($targetUrl) {
	//header('HTTP/1.1 401 Unauthorized');
	$nexturl = repos_getSelfRoot().'/?logout&go='.rawurlencode('?login');
	$presentation = new Presentation();
	$presentation->assign('nexturl', $nexturl);
	$presentation->assign('start', getParent(getWebapp())); // not the same host if the repository is ssl
	$presentation->assign('targeturl', $targetUrl);
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/failed'));
}

function loginAndRedirectToHomeDir() {
	$repo = getRepositoryUrl().'/';
	if (isHttps($repo)) {
		enforceSSL();
	}
	if (isLoggedIn()) {
		$home = getHomeDir($repo);
		// now when we have the username we can test if the login was ok
		// (user does not have access to repository root)
		if (verifyLogin($home)) {
			login_setUserSettings();
			header("Location: " . $home);
		} else {
			showLoginFailed($home);
		}
	} elseif (isset($_GET['login']) && $_GET['login'] == 'user') {
		$realm = getAuthName($repo);
		if(!$realm) {
			$check = '<a href="'.getWebapp().'test/headers/?check='.rawurlencode($repo).'">Check HTTP headers</a> for 401 Authorization Required.';
			trigger_error("Error: No login realm was found for repository $repo. \n$check", E_USER_ERROR);
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

