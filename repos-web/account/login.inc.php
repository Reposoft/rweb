<?php
/**
 * Login integration (c) 2004-2007 Staffan Olsson www.repos.se
 *
 * repos.se versions 1.x has no internal user database.
 * Instead, all security relies on the repository security,
 * which relies on the most tested web server there is.
 * The repository can have any authentication backend, such
 * as flat file, database or LDAP directory.
 *
 * Repos authenticates every request against the 'target' resource
 * which is the file or filder that the user wants to reach.
 * The 'target' is the central concept here, being the identification
 * of a resource, and a URL that the the user has direct access to.
 * <code>
 * getTarget(); // returns the absolute path from the repository root.
 * getTargetUrl(); // returns the absolute URL as the user knows it.
 * </code>
 * If login passed, use <code>getReposUser()</code> to get the account name.
 *
 * Repos checks the HTTP headers of this direct access URL,
 * and requires the same authentication for the current page.
 *
 * This way Repos is compatible with all browsers and subversion clients.
 * Any HTTP capable application can use this to integrate to repos.
 *
 * All scripts working with contents are expected to include this file.
 * Other scripts can include only repos.properties.php instead.
 *
 * If 'target' resource can be resolved using getTarget().
 * Require authentication using <code>tagetLogin();</code>
 *
 * Login functions return true if login was successful.
 * Login always requires a target URL. This URL is used to find
 * the AuthName. If the target resource does not require login,
 * the functions will return true without showing a login box.
 * The credentials will then be empty.
 *
 * Note that internally, URLs should never be encoded.
 *
 * This script produces HTTP headers, so it must be included before
 * any other output. The script does not print anything to the output stream,
 * it uses trigger_error on unexpected conditions.
 *
 * @package account
 * @version $Id$
 */

if (!function_exists('getRepository')) require(dirname(dirname(__FILE__)).'/conf/repos.properties.php');
if (!class_exists('ServiceRequest')) require(dirname(dirname(__FILE__)).'/open/ServiceRequest.class.php');
// not dependent on the System class, this is only web functions

// reserved username value for not-logged-in
define('LOGIN_VOID_USER', '0');

// transparent workaround for the svn index anomaly that root path is "/" but no other paths have trailing slash
{
	$uri = $_SERVER['REQUEST_URI'];
	if (false !== $i = strpos($uri, 'target=//')) {
		header('Location: '.substr($uri, 0, $i+7).'/'.substr($uri, $i+9)); exit;
	}
	if (false !== $i = stripos($uri, 'target=%2F%2F')) {
		header('Location: '.substr($uri, 0, $i+7).'/'.substr($uri, $i+13)); exit;
	}
}

/**
 * Converts a URL to a logout with redirect to the original destination.
 */
function asLogoutUrl($href) {
	if (!preg_match('/^(\/|\w+:\/\/).*/', $href)) {
		trigger_error('Logout requires absolute URL, got '.$href, E_USER_ERROR);
	}
	return '/?logout&go='.rawurlencode($href);
}

/**
 * Redirect to secure URL if current URL is not secure.
 * This should always be done for BASIC authentication.
 * This is skipped if query param SKIP_SSL is set to one 1.
 * Note that this can also be done in the Apache server config.
 */
function enforceSSL() {
	if (isset($_GET['SKIP_SSL']) && $_GET['SKIP_SSL'] == 1) {
		return;
	}
	if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) {
		// OK, this is secure, or SSL disabled
	} else {
		$secureUrl = str_replace('://','s://', getSelfUrl().'?'.getSelfQuery());
		header("Location: $secureUrl");
		exit;
	}
}

/**
 * Require login to the resource specified as target according to getTarget(),
 * request authentication if not authenticated already.
 */
function targetLogin() {
	$targetUrl = getTargetUrl();
	if (isLoggedIn()) {
		if(verifyLogin($targetUrl)) {
			// Do nothing. Parent script resumes operation.
		} else {
			header('HTTP/1.0 401 Unauthorized'); // TODO should use the same header that we got from targetUrl
			trigger_error("Access denied for target resource $targetUrl", E_USER_WARNING); // expecting exit
		}
	} else {
		// Just BASIC authentication, no extra info page like in account/login/index.php
		$realm = getAuthName($targetUrl);
		if ($realm) {
			askForCredentials($realm);
			trigger_error('Need authentication for resource '.$targetUrl, E_USER_NOTICE);
			exit;
		} else {
			// target does not need login. Parent script resumes operation.
		}
	}
}

/**
 * @param String $targetUrl absolute URL, NOT encoded.
 * 	Characters like åäö, spaces and % are accepted.
 * @return true if the current user can access the resource, false if not
 * does trigger_error if the resource can not be used for authentication
 */
function verifyLogin($targetUrl) {
	if (!isRepositoryUrl($targetUrl)) {
		trigger_error('Target URL is not a repository resource. Can not validate login using '.$targetUrl, E_USER_ERROR);
	}
	// $targetUrl = urlEncodeNames($targetUrl);
	$user = getReposUser();
	if (!$user) {
		trigger_error('No user credentials given. Can not validate login.', E_USER_ERROR);
	}
	$request = new ServiceRequest($targetUrl);
	$request->setSkipBody();
	$s = $request->exec();
	if ($s==301) login_followRedirect($targetUrl, $request->getResponseHeaders());
	// 404 means we _are_ allowed access to a parent folder (make sure this assumtion is verified on the server)
	if ($s==404) return true;
	// accepted codes
	if ($s==200) return true;
	if ($s==401 || $s==403) return false;
	// note that 500 is returned if SvnParentPath is used and the target points to a nonexisting repository
	if ($s==500 && $targetUrl == getRepository().'/'.$user.'/') return false; // make default login urls work with SvnParentPath
	// unknown status, probably 500
	trigger_error("The target URL '$targetUrl' can not be used to validate login. Returned HTTP status code '$s'.", E_USER_ERROR);
	return false;
}

/**
 * @return the parent folder (with trailing slash) for an absolute URL.
 */
function _login_getParentUrl($url) {
	return substr($url, 0, strrpos(rtrim($url,'/'), '/')).'/';
}

/**
 * Mimics redirects from repository for target parameters.
 * Useful when target is a folder but does not end with slash.
 * Note that this can not handle old revisions of folders that have been deleted.
 */
function login_followRedirect($fromUrl, $headers) {
	$location = rawurldecode($headers['Location']);  // location header is urlencoded
	$from = substr($fromUrl, strlen(getRepository()));
	$to = substr($location, strlen(getRepository()));
	$url = getSelfUrl().'?'.getSelfQuery();
	$url = str_replace($from, $to, $url);
	header('Location: '.urlEncodeNames($url));
	exit;
}

/**
 * Looks for an existing resource by checking each parent of the target url.
 * @param optional valiable to store the first non-404 status code in when returning
 * @return the url of a resource that exists, false if none found, same as URL if URL exists
 */
function login_getFirstNon404Parent($url, &$status) {
	$status = _login_getHttpStatus($url);
	while ($status==404) {
		$url = _login_getParentUrl($url);
		if (!$url) return false;
		$status = _login_getHttpStatus($url);
	}
	return $url;
}

/**
 * @return the url with username:password
 */
function _getLoginUrl($urlWithoutLogin) {
	if (getReposUser()) {
		return str_replace("://","://" . getReposUser() . ":" .  _getReposPass() . "@", $urlWithoutLogin);
	}
	return $urlWithoutLogin;
}

/**
 * @param targetUrl The absolute URI of the resource
 * @return realm (string) authentication realm, or FALSE if login not required
 */
function getAuthName($targetUrl) {
	return login_getAuthNameFromRepository($targetUrl);
}

/**
 * Uses HTTP to check authentication headers for a resource.
 * Returns false if the targetUrl does not request authenticatoin
 */
function login_getAuthNameFromRepository($targetUrl) {
	$s = new ServiceRequest($targetUrl, array(), false);
	$s->setSkipBody();
	$s->exec();
	if ($s->getStatus()!=401) {
		return false;
	}
	$headers = $s->getResponseHeaders();
	$auth = $headers['WWW-Authenticate'];
	if (preg_match('/realm="([^"]*)"/', $auth, $m)) {
		return $m[1];
	}
	trigger_error("Repos error: realm not found in authentication string: $auth", E_USER_ERROR);
}

/**
 * Convenience method to read response headers using the ServiceRequest class
 * @return the HTTP status code for the URL, with optional user credentials
 */
function _login_getHttpStatus($targetUrl) {
	$s = new ServiceRequest($targetUrl);
	$s->setSkipBody();
	$s->exec();
	return $s->getStatus();
}

// ----- resource URL retreival functionality -----

/**
 * Decodes query string parameters that may be cleartext paths (as in svnindex)-
 * @param array the server variable, for example $_GET
 * @param name the parameter name, for example 'path' as in ?path=/here/now/
 * Returns the non-escaped URL or path or filename or whatever
 * @deprecated Currently this method does no conversion, it only checks parameter encoding, which shouldn't be needed.
 */
function login_decodeQueryParam($array, $name) {
	$v = $array[$name];
	if (mb_detect_encoding($v, 'UTF-8, ISO-8859-1')=='ISO-8859-1') {
		trigger_error("The value of parameter '$name' ($v) is not valid UTF-8", E_USER_ERROR);
	}
	return $v;
}

/**
 * @return true if there is a target parameter
 */
function isTargetSet() {
	return isset($_REQUEST['target']);
}

/**
 * Target is the absolute url of a repository resource, from repository root
 * (thus starting with '/')
 * @return target in repository from query paramters WITH tailing slash if it is a directory, false if 'target' is not set
 */
function getTarget() {
	if(!isTargetSet()) trigger_error('This page is expected to have a "target", but the parameter was not set.', E_USER_WARNING);
	return login_decodeQueryParam($_REQUEST,'target');
}

/**
 * Target url is resolved from query parameters.
 * Not that this returns the non-SSL url. Use
 * getRepositoryUrl().getTarget() for URL with same protocol as current page.
 * @param target Optional target path, if not set then getTarget() is used.
 * @return Full url of the file or directory this request targets, Not urlencoded.
 */
function getTargetUrl($target=null) {
	if ($target==null) $target = getTarget();
	if (strlen($target)<1) return false;
    return getRepository() . $target;
}

// ----- authentication functionality -----

/**
 * Sets the HTTP headers to ask for username and pasword.
 *
 * Caller may optionally print out HTML after this method is called,
 * to display a message if the user hits cancel.
 *
 * Always using Basic auth. The credentials can be used
 * for Digest auth to the target resource anyway.
 */
function askForCredentials($realm) {
	if (headers_sent()) { // This should normally not happen, because only POST requests should use background processing
		trigger_error('Browser should have been automatically redirected to login page before contents were sent.', E_USER_ERROR);
	}
	header('WWW-Authenticate: Basic realm="' . $realm . '"');
	header('HTTP/1.1 401 Authorization Required');
}

/**
 * @return true if HTTP login credentials are present and username is not void
 */
function isLoggedIn() {
	return (getReposUser() !== false);
}

/**
 * @return logged in username, or false if no user (but use isLoggedIn to check)
 */
function getReposUser($force = null) {
	static $_user = null;
	if ($force !== null) $_user = $force; // testing
	if ($_user !== null) return $_user;
	// read the auth only once per request
	if (isset($_SERVER['PHP_AUTH_USER'])) {
		 $u = $_SERVER['PHP_AUTH_USER'];
		 if ($u == LOGIN_VOID_USER) {
		 	$_user = false;
		 } else {
		 	validateUsername($u);
		 	$_user = $u;
		 }
	} else {
		$_user = false;
	}
	return $_user;
}
function _getReposPass($force = null) {
	static $_pass = null;
	if ($force !== null) $_pass = $force; // testing
	if ($_pass !== null) return $_pass;
	if (!isLoggedIn()) {
		$_pass = false;
	} else {
		$_pass = $_SERVER['PHP_AUTH_PW'];
	}
	return $_pass;
}
function getReposAuth() {
	return "This function is deprecated. Will be removed in 1.0"; // $_SERVER['HTTP_AUTHORIZATION'];
}
/**
 * Triggers error on invalid usernames, this protects from urlinjection when username is used in paths
 * TODO when there is a GUI for creating users, there should be a lot more restrictions than this function has.
 * @return true if the provided string is a valid username
 */
function validateUsername($username) {
	return true; // create a UsernameValidator
	if (strContains($username, '/')) trigger_error('Invalid username. Can not contain "/".');
	if (strContains($username, '\\')) trigger_error('Invalid username. Can not contain "\\".');
	if (strContains($username, '"') || strContains($username, '\'') || strContains($username, '`'))
		trigger_error('Invalid username. Can not contain quotes.');
	return true;
}

/**
 * @return true if the server's PHP installation has the SSL extension
 */
function login_isSSLSupported() {
	return function_exists('openssl_open');
}

// set a cookie to tell javascripts which user this is
// this is set in cleartext under the USERNAME_KEY because it is never used for authentication
function login_setUserSettings() {
	$user = getReposUser();
	if (empty($user)) {
		trigger_error('User not logged in. Can not store username.', E_USER_ERROR);
	}
	// note that php encodes spaces to "+" instead of %20 as in spec
	//setcookie(USERNAME_KEY, $user, 0, '/');
	header('Set-Cookie: '.USERNAME_KEY.'='.rawurldecode($user).'; path=/');
}

function login_clearUsernameCookie() {
	setcookie(USERNAME_KEY, '', time()-1, '/');
}

?>
