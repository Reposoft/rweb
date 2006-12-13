<?php
/************** repos.se PHP authentication *****************
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
 * - getTarget() returns the absolute path from the repository root.
 * - getTargetUrl() returns the absolute URL as the user knows it.
 * If login passed, use getReposUser() to get the account name.
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
 * Also designed for transparent login in other PHP applications that
 * use online access to resources, such as PHPiCalendar and phpThumb.
 *
 * If 'target' resource can be resolved using getTarget(),
 * login will be done automatically using tagetLogin();
 *
 * If 'target' is not known the standard way, login explicitly
 * by calling:
 *  - login($targetUrl);
 *
 * To do authentication without accessing a specific resource, do:
 *  - askForCredentialsAndExit($realm);
 *
 * Login functions return true if login was successful.
 * Login always requires a target URL. This URL is used to find
 * the AuthName. If the target resource does not require login,
 * the functions will return true without showing a login box.
 * The credentials will then be empty.
 * 
 * Note that internally, URLs should never be encoded.
 * If a subversion command requires an encoded URL, it should be
 * encoded before it's passed to escapeArgument.
 *
 * Nomenclature throughout the repos PHP solution:
 * 'target' absolute url from repository root to target resource
 * 'targeturl' URI of the resource, permanent location as an HTTP url
 * 'repo' repository root URI, uniquely defines a repository
 * 
 * This script produces HTTP headers, so it must be included before
 * any other output. The script does not print anything to the output stream,
 * it uses trigger_error on unexpected conditions.
 * 
 * @package account
 */
require_once(dirname(dirname(__FILE__)) . '/conf/repos.properties.php');
if (!class_exists('ServiceRequest')) require(dirname(dirname(__FILE__)).'/open/ServiceRequest.class.php');

// currently we include the old svn functions from here, to convert transparently
require(dirname(dirname(__FILE__)).'/open/SvnOpen.class.php');

// do automatic login if a target is specified the standard way
if (isTargetSet()) {
	targetLogin();
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
		$secureUrl = str_replace('://','s://', repos_getSelfUrl().'?'.repos_getSelfQuery());
		header("Location: $secureUrl");
		exit;
	}
}

/**
 * Login to the resource specified as target according to getTarget()
 */
function targetLogin() {
	$targetUrl = getTargetUrl();
	login($targetUrl);
}

/**
 * @param String targetUrl The resource to authenticate to
 */
function login($targetUrl) {
	if (isLoggedIn()) {
		if(verifyLogin($targetUrl)) {
			// Do nothing. Parent script resumes operation.
		} else {
			header('HTTP/1.0 401 Unauthorized'); // TODO should use the same header that we got from targetUrl
			trigger_error("Access denied for target resource $targetUrl", E_USER_WARNING); // expecting exit
			exit; // TODO remove extra 'exit' when we're sure this header really is sent and not overwritten
		}
	} else {
		$realm = getAuthName($targetUrl);
		if ($realm) {
			askForCredentials($realm);
			// causes a refresh
		} else {
			// target does not need login. Parent script resumes operation.
		}
	}	
}

/**
 * @param targetUrl absolute URL, not encoded, characters like åäö and % are accepted
 * @return true if the current user can access the resource, false if not
 * does trigger_error if the resource can not be used for authentication
 */
function verifyLogin($targetUrl) {
	if (!isRepositoryUrl($targetUrl)) {
		trigger_error('Target URL is not a repository resource. Can not validate login using '.$targetUrl, E_USER_ERROR);
	}
	$targetUrl = urlEncodeNames($targetUrl);
	$user = getReposUser();
	if (!$user) {
		trigger_error('No user credentials given. Can not validate login.', E_USER_ERROR);
	}
	$s = getHttpStatus($targetUrl, $user, _getReposPass());
	// allow authentication with parent if the current target is no longer in the repository
	if ($s==404) login_getFirstNon404Parent(getParent($targetUrl), $s);
	// accepted codes
	if ($s==200) return true;
	if ($s==401 || $s==403) return false;
	trigger_error("The target URL '$targetUrl' can not be used to validate login. Returned HTTP status code '$s'.", E_USER_ERROR);
	return false;
}

/**
 * Looks for an existing resource by checking each parent of the target url.
 * @param optional valiable to store the first non-404 status code in when returning
 * @return the url of a resource that exists, false if none found, same as URL if URL exists
 */
function login_getFirstNon404Parent($url, &$status) {
	$user = null;
	$pass = null;
	if (isRepositoryUrl($url)) {
		$user = getReposUser();
		$pass = _getReposPass();
	}
	$status = getHttpStatus($url, $user, $pass);
	while ($status==404) {
		$url = getParent($url);
		if (!$url) return false;
		$status = getHttpStatus($url, $user, $pass);
	}
	return $url;
}

/**
 * Tries a resource path in current HEAD, for the current user, returning status code.
 * @param $target the path in the current repository; accepts folder names without tailing slash.
 * @return 0 = does not exist, -1 = access denied, 1 = folder, 2 = file, boolean FALSE if undefined
 */
function login_getResourceType($target) {
	$url = getTargetUrl($target);
	if (substr_count($url, '://')!=1) trigger_error("The URL \"$url\" is invalid", E_USER_WARNING); // remove when not frequent error
	$request = new ServiceRequest($target);
	$request->setSkipBody();
	$request->exec();
	$s = $request->getStatus();
	$headers = $request->getResponseHeaders();
	if ($s==301 && $headers['Location']==$url.'/') return 1;
	if ($s==404) return 0;
	if ($s==403) return -1;
	if ($s==200) return _isHttpHeadersForFolder($headers) ? 1 : 2;
	return false;
}

function _isHttpHeadersForFolder($headers) {
	// make headers test to validate that this works
	if (!$headers['Content-Type']='text/xml') return false;
	return !isset($headers['Content-Length']);
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
 * @return realm (string) authentication realm or false if login not required
 * TODO this method can no longer be used to check if authentication is needed. Create a method isProtected?
 */
function getAuthName($targetUrl) {
	$conf = getConfig('repo_realm');
	if ($conf && strpos($targetUrl, $conf)==0) {
		return $conf;
	}
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
	if(ereg('realm="([^"]*)"', $auth, $regs)) {
		return $regs[1];
	}
	trigger_error("Repos error: realm not found in authentication string: $auth", E_USER_ERROR);
}

/**
 * @return the HTTP status code for the URL, with optional user credentials
 * @deprecated use ServiceRequest class, user and password parameter to this method is no longer effective
 */
function getHttpStatus($targetUrl, $user=null, $pass=null) {
	$s = new ServiceRequest($targetUrl);
	$s->setSkipBody();
	$s->exec();
	return $s->getStatus();
}

/**
 * Reads the HTTP response headers for a URL.
 * For a method that handles parameters, see requestService();
 * @deprecated use ServiceRequest class directly, user and password parameter to this method is no longer effective
 */
function getHttpHeaders($targetUrl, $user=null, $pass=null) {
	if (substr_count($targetUrl, '/')<3) trigger_error("Can not check headers of $targetUrl, because it is not a valid resource", E_USER_ERROR);
	$s = new ServiceRequest($targetUrl);
	$s->setSkipBody();
	$s->exec();
	return $s->getResponseHeaders();
}

// ----- resource URL retreival functionality -----

/**
 * Decodes query string parameters that may be cleartext paths (as in svnindex)
 * @param array the server variable, for example $_GET
 * @param name the parameter name, for example 'path' as in ?path=/here/now/
 * Returns the non-escaped URL or path or filename or whatever
 */
function login_decodeQueryParam($array, $name) {
	$v = rawurldecode($array[$name]);
	if (mb_detect_encoding($v, 'UTF-8, ISO-8859-1')=='ISO-8859-1') {
		trigger_error("The value of parameter '$name' ($v) is not valid UTF-8", E_USER_ERROR);
	}
	return $v;
}

/**
 * @return true if the target is a file, false if it is a folder or undefined
 * @deprecated use isFile($path) directly instead
 */
function isTargetFile() {
	return (isFile(getTarget()));
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
	if(!isTargetSet()) trigger_error('This page is expected to have a "target", but the parameter was not set.');
	return login_decodeQueryParam($_REQUEST,'target');
}

/**
 * @return revision number from parameters (safe as command argument), false if not set
 */
function getRevision($rev = false) {
	if (!$rev) {
		if(!isset($_GET['rev'])) {
			return false;
		}
		$rev = $_GET['rev'];
	}
	if (is_numeric($rev)) {
		return $rev;
	}
	$accepted = array('HEAD');
	if (in_array($rev, $accepted)) {
		return $rev;
	}
	trigger_error("Error. Revision number '$rev' is not valid.", E_USER_ERROR);
}

/**
 * @param String fullUrl the URL of a repository resource
 * @param String pathFromRepoRoot the resource path, absolute from repository root
 * @return repository url (to root) with no tailing slash.
 *   Returns false if url is empty or if path is not part of url. 
 * @deprecated not used
function getRepoRoot($fullUrl,$pathFromRepoRoot) {
	return substr($fullUrl, 0 , strpos($fullUrl, $pathFromRepoRoot));
}
 */

/**
 * Target url is resolved from query parameters
 * @param target Optional target path, if not set then getTarget() is used.
 * @return Full url of the file or directory this request targets
 *  Not urlencoded.
 */
function getTargetUrl($target=null) {
	if ($target==null) $target = getTarget();
	if (strlen($target)<1) return false;
    return getRepository() . $target;
}

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
	header('WWW-Authenticate: Basic realm="' . $realm . '"');
	header('HTTP/1.1 401 Authorization Required');
}

/**
 * Once you ask for credentials the browser will send the request again with credentials.
 */
function askForCredentialsAndExit($realm) {
	askForCredentials($realm);
	// does not show a message on cancel
	exit;
}

/**
 * @return true if HTTP login credentials are present and username is not "void"
 */
function isLoggedIn() {
	return isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER']!='void';
}

/**
 * @return logged in username, or false if no user (but use isLoggedIn to check)
 */
function getReposUser() {
	if (!isLoggedIn()) return false;
	validateUsername($_SERVER['PHP_AUTH_USER']);
	return $_SERVER['PHP_AUTH_USER'];
}
function _getReposPass() {
	if (!isLoggedIn()) return false;
	return $_SERVER['PHP_AUTH_PW'];
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
	setcookie(USERNAME_KEY, $user, 0, '/');
	
	if (!isset($_COOKIE[THEME_KEY])) {
		$style = '';
		// temporarily suggest PE theme for some users
		if ($user=='svensson' || $user=='pe') { 
			$style = 'pe';
		}
		setcookie(THEME_KEY, $style, time()+7*24*3600, '/');	
	}
}

function login_clearUsernameCookie() {
	setcookie(USERNAME_KEY, '', time()-1, '/');
}

?>
