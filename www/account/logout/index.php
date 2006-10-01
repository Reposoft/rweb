<?php
// HTTP authentication logout probably needs to be done in server root
// simply include this file from ?logout flow in root index.php

require( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__)) . "/login.inc.php" );

if (isset($_GET['logout']) && $_GET['logout']=='verify') {
	if (isset($_SERVER['PHP_AUTH_USER'])) {
		if ($_SERVER['PHP_AUTH_USER'] == 'void') {
			doLogoutVoid();
		} else {
			showCouldNotLogOutPage();
		}
	} else {
		showAfterLogoutPage();
	}
} else {
	if (isset($_SERVER['PHP_AUTH_USER'])) {
		doLogout();
	} else {
		// the browser must first see the authentication headers for this page to allow logout using 401 headers
		askForCredentialsBeforeLogout();
	}
}

function askForCredentialsBeforeLogout() {
	// does exactly like in login
	$repo = getRepositoryUrl();
	$realm = getAuthName($repo);
	if(!$realm) {
		trigger_error("Error: No login realm was found for repository $repo");
		exit;
	}
	askForCredentials($realm);
}

function doLogout() {
	header('HTTP/1.1 401 Unauthorized');
	showLoggingOutPage();
}

function doLogoutVoid() {
	header('HTTP/1.1 401 Unauthorized');
	showAfterLogoutPage();
}

// --- logout pages ---

// where to redirect browser when logout is done or not needed
function getAfterLogoutUrl() {
	if (isset($_GET['go'])) {
		return repos_getSelfRoot().rawurldecode($_GET['go']);
	}
	return getWebapp();
}

function getVerifyUrl() {
	$url = repos_getSelfUrl() . '?logout=verify';
	if (isset($_GET['go'])) {
		$url .= '&go='.$_GET['go'];
	}
	return $url;
}

function showAfterLogoutPage() {
	login_clearUsernameCookie();
	$next = getAfterLogoutUrl();
	header("Location: $next");
}

function showLoggingOutPage() {
	$nexturl = getVerifyUrl();
	$presentation = new Presentation();
	$presentation->assign('nexturl', $nexturl);
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/index'));
}

function showCouldNotLogOutPage() {
	if (strstr ($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
		// redirect including a password is illegal in IE
		$presentation = new Presentation();
		$presentation->assign('nexturl', getAfterLogoutUrl());
		$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/logout-ie'));
	} else {
		// redirecting to the exact same url with user 'void', expecting the next 401 header to make browser enough confused to clear auth cache
		$logout_url = str_replace('://', '://void:LoggedOut@', getVerifyUrl());
		header("Location: $logout_url");
	}
}

?>
