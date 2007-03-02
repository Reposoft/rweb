<?php
/**
 * Repos logic shared by all PHP pages.
 *
 * Behaviour is configurable in the file
 * ../../repos-conf/repos.properties
 * 
 * Output (headers AND response) is controlled by either Report or Presentation.
 * 
 * Global functions in repos start with "get", "is" (+ some "str"), and are found
 * in this script or in System.class.php.
 * 
 * @see Report
 * @see Presentation
 * @package conf
 */

$_repos_config = parse_ini_file( _getPropertiesFile(), false );
// not dependent on the System class, this is only functions related to the config

// ----- global settings -----
// PHP4 does not have exceptions, so we use 'trigger_error' as throw Exception.
// - code should not do 'exit' after trigger_error, because that does not allow unit testing.
// - code should report E_USER_ERROR for server errors and E_USER_WARNING for user errors
function reportError($n, $message, $file, $line) {
	$trace = _getStackTrace();
	if (function_exists('reportErrorToUser')) { // formatted error reporting
		call_user_func('reportErrorToUser', $n, $message, $trace);
	} else {
		reportErrorText($n, $message, $trace);
	}
}
// default error reporting, for errors that occur before presentation is initialized
function reportErrorText($n, $message, $trace) {
	if ($n!=2048) { // E_STRICT not defined in php 4
		if ($n==E_USER_ERROR) header('HTTP/1.1 500 Internal Server Error');
		echo("Unexpected error (type $n): $message\n<pre>\n$trace</pre>");
		exit;
	}
}
set_error_handler('reportError');

// cookie settings
define('REPO_KEY', 'repo');
define('USERNAME_KEY', 'account');
define('LOCALE_KEY', 'lang');
define('THEME_KEY', 'style'); // to match stylesheet switcher
define('WEBSERVICE_KEY', 'serv'); // html, json, xml or text

// parameter conventions
define('SUBMIT', 'submit'); // identifies a form submit for both GET and POST

// --- application selfcheck, can be removed in releases (integration tests should check these things) ---
if (!isset($_repos_config['repositories'])) trigger_error("No repositories configured");
if (!isset($_repos_config['repos_web'])) trigger_error("Repos web applicaiton root not specified in configuration");
function _denyParam($name) { if (isset($_GET[$name]) || isset($_POST[$name])) trigger_error("The parameter '$name' is reserved.", E_USER_ERROR); }
_denyParam(REPO_KEY);
_denyParam(USERNAME_KEY);
_denyParam(LOCALE_KEY);
_denyParam(THEME_KEY);
if (get_magic_quotes_gpc()!=0) { trigger_error("The repos server must disable magic_quotes"); } // tested in server test

// ------ local configuration ------

/**
 * Config value getter
 * @param key the configuration value key, as in repos.properties
 * @return the value corresponding to the specified key. False if key not defined.
 */ 
function getConfig($key) {
	// temporary selfcheck
	if ($key=='repo_url') trigger_error("Use getRepository to get the URL");
	if ($key=='repos_web') trigger_error("Use getWebapp to get web root URL");
	//
	return _getConfig($key);
}

function _getConfig($key) {
	global $_repos_config;
	if (isset($_repos_config[$key]))
		return ($_repos_config[$key] );
	return false;	
}

/**
 * Returns the root URL of the repository that the current user is working with.
 * This is the only folder in repos that is _not_ returned with a tailing slash,
 * the reason being that target URLs are defined as absolute URLs from repository root.
 *
 * If a REPO_KEY request parameter or cookie exists, the value of it is returned.
 * If not, configuration is used. If there is many repositories, the one matching
 * the http referrer is used.
 * @return Root url of the repository for this request, no tailing slash. Not encoded.
 */
function getRepository() {
	return _getConfig('repositories');
	// --- disabled multi-repository logic ---
	// 1: query string or cookie
	if (isset($_REQUEST[REPO_KEY])) return $_REQUEST[REPO_KEY];
	if (isset($_COOKIE[REPO_KEY])) return $_COOKIE[REPO_KEY];
	// 2: referer that matches one of the configured repositories
	$r = getConfig('repositories');
	if (!strContains($r, ',')) return $r;
	$ref = getHttpReferer();
	$all = array_map('trim', explode(',', $r));
	foreach ($all as $a) if (strBegins($ref, $a)) return $a;
	return $all[0];
}

/**
 * @return boolean true if the current client uses SSL
 */
function isSSLClient() {
	return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
}

/**
 * Internally URLs have the default protocol, usually 'http'
 *  - this function is for transforming to the browser's protocol.
 * Note that this is done auomatically for getWebapp().
 * @param String $url the URL that should be presented to the user
 * @return String the URL with the current protocol for navigation.
 */
function asLink($url) {
	if (isSSLClient() && substr($url, 0, 5)=='http:') {
		return 'https:'.substr($url, 5);
	}
	return $url;
}

/**
 * Returns the URL to the root folder of the web application.
 * Can be a complete URL with host and path, as well as an absolute URL from server root.
 * It is assumed that 'repos_web' is a non-SSL url.
 * @return String absolute url to the repos web application URL, ending with slash
 */
function getWebapp() {
	return _getConfig('repos_web');
}

/**
 * Gets the referer URL that the browser might send.
 * @return String HTTP_REFERER if there is one, false otherwise.
 */
function getHttpReferer() {
    if (isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) > 5) {
		return $_SERVER['HTTP_REFERER'];
	}
    return false;
}

// ----- helper functions for pages to refer to internal urls -----

/**
 * @return The url to the host of this request, 
 * without tailing slash because absolute urls should be appended.
 */
function getSelfRoot() {
	$url = 'http';
	if(isSSLClient()) $url .= 's';
	$url .= '://' . $_SERVER['SERVER_NAME'];
	if($_SERVER['SERVER_PORT']==80 || $_SERVER['SERVER_PORT']==443) {
		// standard port number, do not append
	} else {
		$url .= ':'.$_SERVER['SERVER_PORT'];
	}
	return $url;
}

/**
 * The current URL without query string
 * Complete url = getSelfUrl().'?'.getSelfQuery();
 */
function getSelfUrl() {
	$uri = $_SERVER['REQUEST_URI'];
	$q = strpos($uri, '?');
	if ($q > 0) {
		$uri = substr($uri, 0, $q);
	}
	// $_SERVER['SCRIPT_NAME'] can not be used because it always contains the filename
	return getSelfRoot() . $uri;
}

/**
 * Current query string, or empty string if there is no query string
 * @return string
 */
function getSelfQuery() {
	if (!isset($_SERVER['QUERY_STRING'])) return '';
	return $_SERVER['QUERY_STRING'];
}

/**
 * Identifies service requests (requests for non-html output).
 * Note that this does not work on error pages, because they don't get the query string.
 * @return true if the current request is for contents, not a user page
 * @see ServiceRequest
 * @see isRequestInternal()
 */
function isRequestService() {
	if (isRequestNoBody()) return true;
	if (!isset($_REQUEST[WEBSERVICE_KEY])) {
		// ErrorDocument in repository might not get the proper superglobals
		if (strpos($_SERVER['REQUEST_URI'], 'serv=') > 0) return true;
		return false;
	}
	return in_array($_REQUEST[WEBSERVICE_KEY],
		array('json','text','xml'));
}

/**
 * @return boolean true if HEAD method request
 */
function isRequestNoBody() {
	return $_SERVER['REQUEST_METHOD']=='HEAD';
}

/**
 * 
 * @return boolean true if the current client is local REMOTE_ADDR (IP is 127.0.0.1)
 */
function isRequestLocal() {
	return isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']=='127.0.0.1';
}

/**
 * Compares any url with the current user's repository root URL.
 * The server might transparently switch between SSL and non-SSL urls,
 * and possibly between different hosts if mirrored,
 * so just comparing strings is not sufficient to decide if an url is from the repository.
 *
 * @param String $url an absolute url, with protocol
 * @return boolean true if the $url belongs to our repository
 */
function isRepositoryUrl($url) {
	// TODO should ignore http/https
	return strpos($url, getRepository())===0;
}

// ---- functions through which all command execution should go ---

/**
 * Enocdes a url for use as href,
 * does not replace URL metacharacters like /, ? and : (for port number).
 * In other words: escapes URL metacharacters, but only if they are not needed to browse to the URL
 */
function urlEncodeNames($url) {
	$q = strpos($url, '?');
	if ($q !== false) return urlEncodeNames(substr($url, 0, $q)).'?'.rawurlencode(substr($url, $q+1));
	$parts = explode('/', $url);
	$i = 0;
	if (strpos($url, '://')!==false) $i = 3; // don't escape protocol and host
	for ($i; $i < count($parts); $i++) {
		$parts[$i] = rawurlencode($parts[$i]);
	}
	$encoded = implode('/', $parts);
	return $encoded;
}

// ----- internal functions -----

/**
 * This (private) function is needed from Command, SvnOpen and _getPropertiesFile.
 * @return the path to read configuratio file from, usually the same as admin_folder (from properties file in that folder)
 */
function _getConfigFolder() {
	static $c = null;
	if (!is_null($c)) return $c;
	$c = dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR;
	if (file_exists($c)) return $c;
	// old location
	$c = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'repos-config'.DIRECTORY_SEPARATOR;
	if (file_exists($c)) return $c;
	trigger_error('Could not find configuration file location ../../admin/ or ../repos-config/', E_USER_ERROR);
}

function _getPropertiesFile() {
	$propertiesFile =  _getConfigFolder().'repos.properties';
	if (!file_exists($propertiesFile)) {
		trigger_error("Repos configuration file $propertiesFile not found.");
		exit;
	}
	return $propertiesFile;
}

function _getStackTrace() {
	$o = '';
	$stack=debug_backtrace();
	$o .= "file\tline\tfunction\n";
	for($i=1; $i<count($stack); $i++) { // skip this method call
		if (isset($stack[$i]["file"]) && $stack[$i]["line"]) {
	    	$o .= "{$stack[$i]["file"]}\t{$stack[$i]["line"]}\t{$stack[$i]["function"]}\n";
		}
	}
	return $o;
}

?>
