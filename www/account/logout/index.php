<?php
/**
 * HTTP authentication logout (c) 2004-2007 Staffan Olsson www.repos.se 
 * 
 * This is not really a supported feature in HTTP authentications,
 * so we need to trick browsers to dropping the credentials.
 * 
 * HTTP authentication logout probably needs to be done in server root
 * simply include this file from ?logout flow in root index.php
 * 
 * @package account
 */
header('Cache-Control: no-cache');

require( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__)) . "/login.inc.php" );

if (isset($_GET['logout']) && $_GET['logout']=='verify') {
	if (isset($_SERVER['PHP_AUTH_USER'])) {
		if ($_SERVER['PHP_AUTH_USER'] == LOGIN_VOID_USER) {
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
		// gives a warning that the site is trying to log in as void user: showCouldNotLogOutPage();
		// would confuse users: askForCredentialsBeforeLogout();
		if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			doLogoutIE();
		} else {
			// Sending only 401 is actually not allowed in HTTP 1.1, and this logout is not effective,
			// but I don't know a better way to do this.
			// The problem is that we can't know at /?logout if the user is authenticated to the repository
			// or if it was just a visitor to a public url.
			// We must assume that there is no authenticated user. The server administrator can set up a
			// rewrite for ?logout or ?svn=logout at repository urls so that credentials will be included to logout.
			header('HTTP/1.1 401 Unauthorized');
			doLogoutVoid(); // clears username cookie (which probably wasn't set if user logged in at repository)
		}
	}
}

function askForCredentialsBeforeLogout() {
	// does exactly like in login
	$repo = getRepository();
	$realm = getAuthName($repo);
	if(!$realm) trigger_error("Error: No login realm was found for repository $repo", E_USER_ERROR);
	askForCredentials($realm);
	// using cancel page from login
	$presentation = Presentation::getInstance();
	$presentation->display($presentation->getLocaleFile(dirname(dirname(__FILE__)).'/login/cancel'));
}

function doLogout() {
	header('HTTP/1.1 401 Unauthorized');
	showLoggingOutPage();
}

/**
 * Say Unauthorized to the user LOGIN_VOID_USER from {@see showCouldNotLogOutPage}.
 */
function doLogoutVoid() {
	header('HTTP/1.1 401 Unauthorized');
	showAfterLogoutPage();
}

// --- logout pages ---

/**
 * where to redirect browser when logout is done or not needed, allow override with 'go' parameter as in login
 */
function getAfterLogoutUrl() {
	$url = '/';
	if (isset($_GET['go'])) {
		$url = $_GET['go']; // already urldecoded by PHP
		if (!preg_match('/^(\/|\w+:\/\/).*/', $url)) $url = getSelfRoot() . $url;
	}
	return $url;
}

function getVerifyUrl() {
	$url = asLink(getSelfUrl()) . '?logout=verify';
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
	$presentation = Presentation::getInstance();
	$presentation->assign('nexturl', $nexturl);
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/index'));
}

function showCouldNotLogOutPage() {
	if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
		doLogoutIE();
	} else {
		// redirecting to the exact same url with user 'void', expecting the next 401 header to make browser enough confused to clear auth cache
		$logout_url = str_replace('://', '://'.LOGIN_VOID_USER.':(logged-out)@', getVerifyUrl());
		header("Location: $logout_url");
	}
}

function doLogoutIE() {
	// redirect including a password is illegal in IE
	$presentation = Presentation::getInstance();
	$presentation->assign('nexturl', getAfterLogoutUrl());
	$presentation->display($presentation->getLocaleFile(dirname(__FILE__).'/logout-ie'));
}

?>
