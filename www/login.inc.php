<?php
/************** repos.se PHP authentication *****************
 * Compatible with java webapp and Apache2 mod_dav_svn access
 *	- Basic authentication
 *  - Enforces HTTPS protocol
 *
 * If 'target' resource can be resolved using getTarget(),
 * login will be done automatically using:
 *  - tagetLogin();
 *
 * If 'target' is not known the standard way, login explicitly
 * by calling:
 *  - login($targetUrl);
 *
 * To do authentication without accessing a specific resource, do:
 *  - askForCredentials($realm);
 *
 * Login functions return true if login was successful.
 * Login always requires a target URL. This URL is used to find
 * the AuthName. If the target resource does not require login,
 * the functions will return true without showing a login box.
 * The credentials will then be empty.
 *
 * This method can be used to check if login is required:
 *  - getAuthName($targetUrl) returns false if login is not required
 *
 * If login passed, use these functions to retrieve credentials:
 *  - getReposUser() username
 *  - getReposPass() cleartext password for repository access
 *  - getReposAuth() encrypted authentication from browser
 *  - getSvnCommand() command line for this user to run 'svn'
 *
 * Also provides functions for shared request processing
 *  - getReferer()   calling page
 *  - getTarget()    in-repository path for this operation
 *  - getTargetUrl() full url to resource if specified
 *  - getRepositoryUrl() repository root URL. If
 *     conf/repos.properties.php has been included before this
 *     script is called, then fallback to settings is enabled.
 */

// *** Headers to disable caching, assumed to be needed on all pages ***
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0
header("Pragma: no-cache");

/**
 * Redirect to secure URL if current URL is not secure.
 * This should always be done for BAsic authentication
 */
function enforceSSL() {
	// *** Enforce HTTPS, unless SKIP_SSL query parameter = 1 ***
	if( (isset($_GET['SKIP_SSL']) && $_GET['SKIP_SSL'] == 1) ||
		isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) {
		// OK, this is secure, or SSL disabled
	} else {
		$secureUrl = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		header("Location: $secureUrl");
		exit;
	}
}

/**
 * Login to a resource specified with repos standard query parameters.
 */
function targetLogin() {
	$targetUrl = getTargetUrl();
	login($targetUrl);
}

/**
 * @param The resource to login to
 */
function login($targetUrl) {
	$realm = getAuthName($targetUrl);
	askForCredentials($realm);
	verifyLogin($targetUrl);
}

/**
 * @return true if the current user can access the resource
 */
function verifyLogin($targetUrl) {
	$urlWithCredentials = getLoginUrl($targetUrl);
	$authHeader = getAuthName($urlWithCredentials);
	return($authHeader===false);
}

/**
 * @return the url with username:password
 */
function getLoginUrl($urlWithoutLogin) {
	if (getReposUser()) {
		return str_replace("://","://" . getReposUser() . ":" .  getReposPass() . "@", $urlWithoutLogin);
	}
	return $urlWithoutLogin;
}

/**
 * Uses HTTP to check authentication headers for a resource.
 * @param targetUrl The absolute URI of the resource
 * @return realm (string) or false if login not required
 */
function getAuthName($targetUrl) {
	$headers = getHttpHeaders($targetUrl);
	print_r($headers);
	if (strpos($headers['status'], '401')===false) {
		return false;
	}
	$auth = $headers['www-authenticate'];
	if(ereg('realm="([^"]*)"', $auth, $regs)) {
		return $regs[1];
	}
	echo("Error, realm not found in authentication string: $auth");
	exit;
}

// abstraction for HTTP operation
function getHttpHeaders($targetUrl) {
	return my_get_headers($targetUrl);
}

// *** url resolution functions, based on query parameters ***

function getReferer() {
    if (isset($_SERVER['HTTP_REFERER'])) return $_SERVER['HTTP_REFERER'];
    return false;
}

/**
 * @return path from repository root, ending with '/', non alphanumerical characters except '/' encoded
 */
function getPath() {
	if(!isset($_GET['path'])) return false;
	$path = urlEncodeNames($_GET['path']);
    $path = rtrim($path,'/').'/';
	return $path;
}

/**
 * Target is the absolute url of a repository resource, from repository root
 * (thus starting with '/')
 * @return target in repository from query paramters WITH tailing slash if it is a directory, false if none of 'target', 'file' and 'path' is defined
 */
function getTarget() {
	if(isset($_GET['target'])) return urlEncodeNames($_GET['target']);
    // append filename if specified
    if(isset($_GET['file'])) return getPath() . rawurlencode($_GET['file']);
    return getPath();
}

/**
 * Repository is resolved using HTTP Referrer with fallback to settings.
 * To find out where root is, query paramter 'path' must be set.
 * @return Root url of the repository for this request, no tailing slash.
 */
function getRepositoryUrl() {
	// 1: query string
	if (isset($_GET['repo'])) {
		return rtrim($_GET['repo'],'/');
	}
	// 2: referer AND query string param 'path'
    $ref = getReferer();
	$path = rtrim(getPath(),'/');
    if ($ref && $path) {
		return getRepoRoot($ref,$path);
    }
	// 3: fallback to default repository
    if(function_exists('getConfig')) {
    	return getConfig('repo_url');
	}
	return false;
}

/**
 * @return repository url (to root) with no tailing slash.
 *   Returns false if url is empty or if if path is not part of url. 
 */
function getRepoRoot($fullUrl,$pathFromRepoRoot) {
	return substr($fullUrl, 0 , strpos($fullUrl, $pathFromRepoRoot));
}

/**
 * Target url is resolved from query parameters
 *  'repo' (fallback to getRepositoryUrl) = root url
 *  'path' = path from repository root
 *  'file' (omitted if not found) = filename inside path
 * @return Full url of the file or directory this request targets
 */
function getTargetUrl() {
	$target = getTarget();
	if (strlen($target)<1) return false;
    return getRepositoryUrl() . $target;
}

function urlEncodeNames($url) {
	$parts = explode('/', $url);
	for ($i = 0; $i < count($parts); $i++) {
		$parts[$i] = rawurlencode($parts[$i]);
	}
	return implode('/', $parts);
}

// *** Authentication ***
// This only asks for username and pasword. When done, it expects the 
// browser to attempt the connection again, with credentials.
// If credentials are set this function stores them so getReposUser() works.
function askForCredentials($realm) {
// does not support digest authentication yet.
// Basic could be converted to digest before forwarding the credentials.
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER']=='void') {
   header('WWW-Authenticate: Basic realm="' . $realm . '"');
   header('HTTP/1.0 401 Unauthorized');
   echo 'Please provide your Repos login';
   exit;
} elseif (false) {
   // There seems to be a number of reasons why you don't get these server variables. Alternative methods include
   // - $_SERVER['REMOTE_USER'] = Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==
   // - list($user, $pw) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
   // For HTTP Authentication to work with IIS, the PHP directive cgi.rfc2616_headers must be set to 0 (the default value).
}
// "PHP_AUTH variables will not be set if external authentication is enabled for that particular page and safe mode is enabled"
// set credentials as constants, so that dependencies are not tied to the server variables
$repos_authentication = array();
$repos_authentication['user'] = $_SERVER['PHP_AUTH_USER'];
$repos_authentication['pass'] = $_SERVER['PHP_AUTH_PW'];
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
	$repos_authentication['auth'] = substr($_SERVER['HTTP_AUTHORIZATION'], 6);
}
// end authentication function
}

function getReposUser() {
	global $repos_authentication;
	return($repos_authentication['user']);
}
function getReposPass() {
	global $repos_authentication;
	return($repos_authentication['pass']);
}
function getReposAuth() {
	global $repos_authentication;
	return($repos_authentication['auth']);
}

// *** Subversion client usage ***

/**
 * Execute svn command like the PHP passthru() function
 * @param cmd The command without the SVN part, for example 'log /url/to/repo'
 * @param cdata True if the result should be enclosed in CDATA tag
 */
function svnPassthru($cmd, $cdata=false) {
	$runthis = getSvnCommand().escapeshellcmd($cmd);
	if($cdata) echo "<![CDATA[\n";
	passthru($runthis,$returnval);
	if($cdata) echo "]]>\n";
	if($returnval) handleSvnError($runthis,$returnval);
}

/**
 * @return Start of command line for executing svn operations, with tailing space
 */
function getSvnCommand() {
	define(SVN_CONFIG_DIR,DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'svn-config-dir');
	$auth = '--username='.getReposUser().' --password='.getReposPass().' --no-auth-cache ';
	$options = '--non-interactive --config-dir '.dirname(__FILE__).SVN_CONFIG_DIR.' ';
	return 'svn '.$auth.$options;
}

/**
 * Errorhandling for SVN execute.
 * WARNING: runs the command agin, and is only suatable for read-only commands
 * (as is this entire PHP solution)
 */
function handleSvnError($executedcmd,$errorcode) {
	echo "<error code=\"$errorcode\">\n";
	if(isset($_GET['DEBUG'])) echo '<exec cmd="'.strtr($executedcmd,'"',"'").'"/>';
	echo "<![CDATA[\n";
	// show error message
	passthru("$executedcmd 2>&1");
	echo "]]>\n";
	echo "</error>\n";
}

// only PHP5 has a get_headers function

function my_get_headers($url ) {
   $url_info=parse_url($url);
   if (isset($url_info['scheme']) && $url_info['scheme'] == 'https') {
	   $port = 443;
	   @$fp=fsockopen('ssl://'.$url_info['host'], $port, $errno, $errstr, 10);
   } else {
	   $port = isset($url_info['port']) ? $url_info['port'] : 80;
	   @$fp=fsockopen($url_info['host'], $port, $errno, $errstr, 10);
   }
   if($fp) {
	   stream_set_timeout($fp, 10);
	   $head = "HEAD ".@$url_info['path']."?".@$url_info['query'];
	   $head .= " HTTP/1.0\r\nHost: ".@$url_info['host']."\r\n\r\n";
	   fputs($fp, $head);
	   while(!feof($fp)) {
		   if($header=trim(fgets($fp, 1024))) {
				   $sc_pos = strpos( $header, ':' );
				   if( $sc_pos === false ) {
					   $headers['status'] = $header;
				   } else {
					   $label = substr( $header, 0, $sc_pos );
					   $value = substr( $header, $sc_pos+1 );
					   $headers[strtolower($label)] = trim($value);
				   }
		   }
	   }
	   return $headers;
   }
   else {
	   return false;
   }
}

// automatic login if a target is specified the standard way
if (getTargetUrl()) {
	targetLogin();
}

?>