<?php
/**
 * Repos configuration logic (c) 2004-1007 Staffan Olsson www.repos.se
 *
 * The core, Repos Web, is only configurable to a minimum.
 * Each configuration entry has a getEntry() and getEntryDefault(),
 * where getEntry may read server variables to get a cutom setting.
 *  
 * This file also configures error handling for Repos application.
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
define('REPOS_VERSION','@Dev@');

// ----- global settings -----

// PHP4 does not have exceptions, so we use 'trigger_error' as throw Exception.
// - code should not do 'exit' after trigger_error, because that does not allow unit testing.
// - code should report: 
//   * E_USER_ERROR for server errors
//   * E_USER_WARNING for user errors, like invalid parameters
//   * E_USER_NOTICE for information, like message to send with authentication headers
function reportError($n, $message, $file, $line) {
	// Allow use of @ error-control operator to suppress errors
	if (error_reporting() == 0) return;
	// We need to support PHP4 so we'll have to accept some PHP5 E_STRICT warnings, like lack of 'static' 
	if (defined('E_STRICT') && $n == E_STRICT) return;	
	// This function does not respect error_reportings settings but now we do for deprecation warnings
	if (defined('E_DEPRECATED') && $n == E_DEPRECATED && !(error_reporting() & E_DEPRECATED)) return;
	// from now on handle all errors
	$trace = _getStackTrace();
	if (function_exists('reportErrorToUser')) { // formatted error reporting
		call_user_func('reportErrorToUser', $n, $message, $trace);
		// note that the custom error reporting is fully responsible for all errors, nothing is done after this
	} else {
		reportErrorText($n, $message, $trace);
	}
	// always stop after errors
	exit(1);
}
// default error reporting, for errors that occur before presentation is initialized
function reportErrorText($n, $message, $trace) {
	// validation error
	if ($n==E_USER_WARNING) {
		if (!headers_sent()) header('HTTP/1.1 412 Precondition Failed');
		echo("Validation error: $message\n<pre>\n$trace</pre>\n\n");
	} else {
		// other errors
		if (!headers_sent() && $n==E_USER_ERROR) header('HTTP/1.1 500 Internal Server Error');
		echo("Runtime error (type $n): $message\n<pre>\n$trace</pre>\n\n");
	}
	// TODO there is some weirdness with notices and Presentation->enableRedirectWaiting
	//  that causes PHP to output headers of vies/index.php as cleartext here
}
set_error_handler('reportError');
// Note from set_error_handler() docs:
//  The following error types cannot be handled with a user defined function:
//  E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, 
//  and most of E_STRICT raised in the file where set_error_handler() is called. 
// So we have to disable E_STRICT if we want php4 compatibility
if (defined('E_STRICT')) {
	error_reporting(error_reporting() ^ E_STRICT);
}

// check essential configuration entries
if (get_magic_quotes_gpc()!=0) { trigger_error("The repos server must disable magic_quotes"); }

// special handling of build flags
define('REPOS_VERSION_ARM','-bubba');
if (strpos(REPOS_VERSION, REPOS_VERSION_ARM)) {
	strpos(phpversion(),'4.3.10')===0 or die('Server mismatch. This Repos release requires PHP for ARM.');
	PHP_OS=='Linux' or die('Distribution mistmatch. This Repos release requires Debian.');
}

// cookie settings
define('USERNAME_KEY', 'account');
define('LOCALE_KEY', 'lang');
define('WEBSERVICE_KEY', 'serv'); // html, json, xml or text

// parameter conventions
define('SUBMIT', 'submit'); // identifies a form submit for both GET and POST, used to plug in behaviour

if (isset($_REQUEST['repo'])) {
	trigger_error('Request parameter "repo" is no longer allowed');
}

// Internally dates should always be UTC. They may be localized in the UI.
date_default_timezone_set('UTC');

// ------ local configuration ------

/**
 * Returns the root URL of the repository that the current user is working with.
 * This is the only folder in repos that is _not_ returned with a tailing slash,
 * the reason being that target URLs are defined as absolute URLs from repository root.
 *
 * If a REPO_KEY request parameter exists, the value of it is returned.
 * If not, server configuration is used.
 * For multi-repo (SVNParentPath) support the value of 'base' request parameter is added
 * to the configured repository.
 *
 * @return Root url of the repository for this request, no tailing slash. Not encoded.
 */
function getRepository() {
	// wrapper that adds support for multiple repositories using 'base' param
	if (isset($_REQUEST['base']) && strlen($_REQUEST['base'])>0) {
		return getRepositoryRoot().'/'.$_REQUEST['base'];		
	}
	return getRepositoryRoot();
}

/**
 * May be used instead of {@link #getRepository} to mark that the returned
 * value will be used for internal access, not for presentation. 
 * This allows repos-web to optimize for faster access to the repository.
 * Typically this is used if the external repository url is https only
 * and the server supports http locally.
 * The logic in this method should avoid dependence on values from client,
 * so that the results can not be manipulated to curcumvent security.
 * Also if this is configurable by server admin it must be made very clear that
 * repos-web security relies on the fact that the internal svn client uses the
 * same repository access url, proxying user authentication.
 * Thus a change from http to file protocol has serious implications.
 * 
 * @return {String} Root url of the repository for this request, no tailing slash. Not encoded.
 */
function getRepositoryInternal() {
	// TODO implement according to unit tests, evaluate in test ssl setup
	return getRepository(); // same behavior as before but code can still mark its intentions by calling getRepositoryInternal
}

/**
 * Internal, getRepository wraps this function and adds support for SVNParentPath.
 */
function getRepositoryRoot() {
	// requet parameter
	// TODO remove (deprecated because it is a security risk) //if (isset($_REQUEST[REPO_KEY])) return $_REQUEST[REPO_KEY];
	// server configuration
	if (isset($_SERVER['REPOS_REPO'])) {
		$repo = $_SERVER['REPOS_REPO'];
		if (strBegins($repo, '/')) $repo = getHost().$repo;
		return $repo;
	}
	return getRepositoryDefault();
}

/**
 * @return String Default repository, Location /svn at current host.
 */
function getRepositoryDefault() {
	return getHost().'/svn';
}

/**
 * Reads the HTTPS server variable, if "on" returns true.
 * @return boolean true if the current client uses SSL
 */
function isSSLClient() {
	return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
}

/**
 * Internally URLs have the default protocol, usually 'http'
 *  - this function is for transforming to the browser's protocol.
 * Note that this is done auomatically for getWebapp().
 * 
 * Protocol is httpS if isSSLClient()==true, meaning that the result
 * is based on server configuration rather than current request
 * (to allow for SSL proxies).
 * 
 * The current implementation does not support custom port number for SSL.
 * 
 * @param String $url the URL that should be presented to the user
 * @return String the URL with the current protocol for navigation.
 *  If the URL is not absolute, only encoding with urlSpecialChars will be done.
 */
function asLink($url) {
	if (isSSLClient() && substr($url, 0, 5)=='http:') {
		$url = 'https:'.substr($url, 5);
		// currently there is no setting for custom https port numer so we use default
		if (strpos($url,':',8) && preg_match('/^(.+)(\:\d+)(\/.*)?$/', $url, $m)) {
			$url = $m[1].(isset($m[3])?$m[3]:'');
		}
	}
	return urlSpecialChars($url);
}

/**
 * Returns the URL to the root folder of the web application.
 * Can be a complete URL with host and path, as well as an absolute URL from server root.
 * It is assumed to be a plain http url, not ssl.
 * @return String absolute url to the repos web application URL, ending with slash
 */
function getWebapp() {
	if (isset($_SERVER['REPOS_WEBAPP'])) return $_SERVER['REPOS_WEBAPP'];
	return getWebappDefault();
}

/**
 * @return String Default webapp URL: /repos-web/
 */
function getWebappDefault() {
	return '/repos-web/';
}

/**
 * Used instead of getWebapp() where a complete URL is nessecary.
 * getWebapp() is always preferred because it gives less problems with http/https.
 * @return String A complete URL to the webapp (never path from root)
 */
function getWebappUrl() {
	$w = getWebapp();
	if (strpos($w,'://')) return $w;
	return getHost().getWebapp();
}

/**
 * Set the access control file used to display startpage.
 * Same syntax as AuthzSVNAccessFile, can be the same file.
 * Without an access file the start page contents will be default.
 * @return String absolute path to the ACL
 */
function getAccessFile() {
	if (isset($_SERVER['REPOS_ACCESS_FILE'])) return $_SERVER['REPOS_ACCESS_FILE'];
	$default = getAccessFileDefault();
	// validating default here, maybe not very smart
	if (!is_file($default)) return false;
	return $default;
}

/**
 * Default access file is admin/repos-access relative to document root
 * @return String absolute path to the standard Repos AccessFile location, which may exist
 */
function getAccessFileDefault() {
	return getParent(getDocroot()).'admin/repos-access';
}

/**
 * @return docroot folder with trailing slash
 */
function getDocroot() {
	return getDocrootDefault();
}

/**get
 * Guess docroot from DOCUMENT_ROOT server variable, with fallback to repos-web's parent folder
 * @return docroot folder with trailing slash
 */
function getDocrootDefault() {
	$docroot = dirname(dirname(dirname(__FILE__)));
	if (isset($_SERVER['DOCUMENT_ROOT'])) {
		$d = $_SERVER['DOCUMENT_ROOT']; // docroot may be incorrect, for exaple with VirtualDocumentRoot
		if (strncmp($_SERVER['SCRIPT_FILENAME'], $d, strlen($d))==0) $docroot = $d;
	}
	if ($docroot && is_dir($docroot)) return $docroot.'/';
	trigger_error('Could not resolve document root. Server parameters must be set.', E_USER_ERROR);
}

/**
 * The locale to be used in command executions on non-Windows servers.
 * Repos uses this only for encoding texts, not date or time formatting.
 * There is usually no reason to change the default.
 * @see System::toShellEncoding for php file encoding (which doesn't always follow system locale)
 * @return String Locale for PHP setlocale() and Linux "locale" command.
 */
function getLocale() {
	if (isset($_SERVER['REPOS_LOCALE'])) return $_SERVER['REPOS_LOCALE'];
	return getLocaleDefault();
}

/**
 * @return String en_US.utf8
 */
function getLocaleDefault() {
	return 'en_US.UTF-8';
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
 * Get the current ReposHost: http URL root (protocol, host and port).
 * To adapt to clients that may use SSL, i.e. remote users, 
 *  call asLink(getHost().'/the/script/path/').
 * @return The url to the host of this request, 
 * without tailing slash because absolute urls should be appended.
 * @deprecated Use the new function name getHost
 */
function getHost() {
	return getSelfRoot();
}

/**
 * @deprecated use getHost
 */
function getSelfRoot() {
	if (isset($_SERVER['REPOS_HOST'])) return $_SERVER['REPOS_HOST'];
	return getHostDefault(); 
}

/**
 * @return default host root url
 */
function getHostDefault() {
	$url = 'http://' . $_SERVER['SERVER_NAME'];
	if (!isSSLClient() && isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']!=80) {
		$url .= ':'.$_SERVER['SERVER_PORT'];
	}
	return $url;
}

/**
 * @return the current request uri (from server root), _not_ urlencoded
 */
function getRequestUri() {
	return rawurldecode($_SERVER['REQUEST_URI']);	// REQUEST_URI is urlencoded
}

/**
 * @return the path to the current script without query string
 */
function getSelfPath() {
	$uri = getRequestUri();
	$q = strpos($uri, '?');
	if ($q > 0) {
		$uri = substr($uri, 0, $q);
	}
	return $uri;
}

/**
 * The current URL without query string
 * Complete url = getSelfUrl().'?'.getSelfQuery();
 */
function getSelfUrl() {
	// $_SERVER['SCRIPT_NAME'] can not be used because it always contains the filename
	return asLink(getHost() . getSelfPath());
}

/**
 * Current query string, or empty string if there is no query string
 * @return string the part of the URI after the "?", or empty string if no query
 */
function getSelfQuery() {
	$uri = getRequestUri();
	$q = strpos($uri, '?');
	if ($q == 0) return '';
	return substr($uri, $q+1);
}

/**
 * The service is the path to the current repos tool.
 * This implementation assumes that webapp is a top level folder in docroot.
 */
function getService() {
	$p = getSelfPath();
	// extract from path
	if ($p == '/') {
		if (strpos(getSelfQuery(),'login')===0) return 'account/login/';
		return 'account/logout/';
	}
	$s = 0;
	// repos-admin and repos-backup alway have the same path (not configurable in 1.2)
	if (!preg_match('/^\/repos-(admin|backup)\//', $p)) {
		$s = strpos($p,'/',1);
	}
	$e = strrpos($p,'/');
	if ($s==$e) return 'home/';
	return substr($p,$s+1,$e-$s);
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
		// This method may be used in CLI mode, in which case there are no service requests
		if (!isset($_SERVER['REQUEST_URI'])) return false;
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
	return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='HEAD';
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

/**
 * Enocdes a url for use as href,
 * does not replace URL metacharacters like /, ? and : (for port number).
 * In other words: escapes URL metacharacters, but only if they are not needed to browse to the URL
 * This is quite equivalent to javascript's encodeURI (see http://www.the-art-of-web.com/javascript/escape/)
 */
function urlEncodeNames($url) {
	$q = strpos($url, '?');
	if ($q !== false) {
		return urlEncodeNames(substr($url, 0, $q)).'?'._urlEncodeQueryString(substr($url, $q+1));
	}
	$parts = explode('/', $url);
	$i = 0;
	if (strpos($url, '://')!==false) $i = 3; // don't escape protocol and host
	for ($i; $i < count($parts); $i++) {
		$parts[$i] = rawurlencode($parts[$i]);
	}
	$encoded = implode('/', $parts);
	return $encoded;
}

/**
 * A minimal escape for characters that the browsers can't urlescape themseleves.
 * This is analogous to the getHref escape in repos xslt.
 */
function urlSpecialChars($url) {
	if ($q = strpos($url, '?')) return urlSpecialChars(substr($url, 0, $q)).'?'.substr($url, $q+1);
	return str_replace(
		array(
		'%',
		'&',
		'#',
		' ',
		'+'),
		array(
		'%25',
		'%26',
		'%23',
		'%20',
		'%2B'),
		$url);
}

function _urlEncodeQueryString($params) {
	$parts = explode('&', $params);
	$q = '';
	foreach ($parts as $part) {
		// encode only the values
		if ($p = strpos($part, '=')) {
			$q .= '&'.substr($part, 0, $p+1).rawurlencode(substr($part, $p+1));
		} else {
			$q .= '&'.$part;
		}
	}
	return substr($q, 1);
}

/**
 * The only character that is valid in filenames and not xml is "&"
 * (and maybe single quote too, but currently we do not handle that).
 * @param String $path path to a file or folder in repository
 * @return String the path for use in XML output
 */
function xmlEncodePath($path) {
	return str_replace('&', '&amp;', $path);
}

// ----- internal functions -----

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
