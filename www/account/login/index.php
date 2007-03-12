<?php
/**
 * Provides HTTP basic login, with authentication, 
 * verified by forwarding the credentials to a repository resource.
 * 
 * @package account
 */
header('Cache-Control: no-cache');

require( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__)) . "/login.inc.php" );

/**
 * Suggests URL that can be used to validate user credentials
 * @param String $repository absolte url to repository resorce, _with_ tailing slash
 * @param String $username The username that tries to login
 * @return array of URLs to test, in order
 */
function getVerifyLoginUrls($repository, $username) {
	$user = getReposUser();
	return array(
		$repository . login_encodeUsernameForURL($user) . '/',
		$repository,
		$repository . 'trunk/'
	);
}

/**
 * If needed, username is encoded for URLs here.
 * The rule of thumb is that internally, nothing should be encoded.
 * URL checking is done by ServiceRequest, which does not want encoded urls.
 */
function login_encodeUsernameForURL($username) {
	return $username;
}

function isHttps($repository) {
	return !(strpos($repository, 'https://')===false);
}

function showUserLogin() {
	$nexturl = getSelfUrl() . "?login=user";
	$presentation = Presentation::getInstance();
	$presentation->assign('nexturl', $nexturl);
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/index'));
}

function showLoginCancelled() {
	$presentation = Presentation::getInstance();
	$presentation->assign('start', getParent(getWebapp())); // not the same host if the repository is ssl
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/cancel'));
}

function showLoginFailed($targetUrl) {
	//header('HTTP/1.1 401 Unauthorized');
	$nexturl = getSelfRoot().'/?logout&go='.rawurlencode('?login');
	$presentation = Presentation::getInstance();
	$presentation->assign('nexturl', $nexturl);
	$presentation->assign('start', getParent(getWebapp())); // not the same host if the repository is ssl
	$presentation->assign('targeturl', $targetUrl);
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/failed'));
}

function loginAndRedirectToHomeDir() {
	$repo = getRepository().'/';
	if (isHttps($repo)) {
		enforceSSL();
	}
	if (isLoggedIn()) {
		$user = getReposUser();
		if (!$user) {
			showLoginFailed('[Error: Username is required but was empty]');
			exit;
		}
		$try = getVerifyLoginUrls($repo, $user);
		foreach ($try as $url) {
			if (verifyLogin($url)) {
				login_setUserSettings();
				header("Location: " . getStartUrl($url));
				return;
			}
		}
		showLoginFailed(implode(", ", $try));
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

/**
 * Builds the URL to redirect the browser to after login.
 *
 * @param String $home the repository home folder of the user, used to verify login
 * @return String absolute URL for redirect
 */
function getStartUrl($home) {
	return getWebapp() . 'open/start/';//.'?home='. rawurlencode($home);
}

loginAndRedirectToHomeDir();

?>

