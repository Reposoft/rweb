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
define('REPOS_VERSION','1.7');

// ----- global settings -----

// Folder where conditionally included customizations can be located
define('ReposCust', isset($_SERVER['REPOS_LOCAL_CUST']) ?
$_SERVER['REPOS_LOCAL_CUST']
: dirname(dirname(dirname(__FILE__))).'/repos-cust/');

/**
 * Load a customization, given id=path, if it exists.
 */
function reposCustomizationInclude($path) {
	$custFile = ReposCust.$path;
	if (file_exists($custFile)) {
		require_once $custFile;
	}
}

// PHP4 does not have exceptions, so we use 'trigger_error' as throw Exception.
// - code should not do 'exit' after trigger_error, because that does not allow unit testing.
// - code should report: 
//   * E_USER_ERROR for server errors
//   * E_USER_WARNING for user errors, like invalid parameters
//   * E_USER_NOTICE for information, like message to send with authentication headers
// - note that errors occuring inside the error handlers are displayed with PHP's default error handler
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

// Sets http header status line based on error characteristics
function reportErrorStatus($n, $message, $trace) {
	if (headers_sent()) {
		return false;
	}
	if (preg_match("/^svn: '(.*)' does not exist in revision (\d+)$/", $message)
		|| preg_match("/^svn: '.*' path not found$/", $message)) {
		header('HTTP/1.1 404 Not Found');
		return 404;
	}
	if ($n==E_USER_WARNING) {
		header('HTTP/1.1 412 Precondition Failed');
		return 412;
	} 
	if ($n==E_USER_ERROR) {
		header('HTTP/1.1 500 Internal Server Error');
		return 500;
	}	
}

// default error reporting, for errors that occur before presentation is initialized
function reportErrorText($n, $message, $trace) {
	reportErrorStatus($n, $message, $trace);
	// validation error
	if ($n==E_USER_WARNING) {
		echo("Validation error: $message\n<pre>\n$trace</pre>\n\n");
	} else {
		// other errors
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

// From now on REPOS_REPO_PARENT should be used for parent path setups and REPOS_REPO for single repo
// mod_dav_svn index sets @base regardless so we have to ignore it for single repo setups
// which is fine in code but not easy to detect from plugins.
// Probably all plugins can just assume multirepo and the backend can handle it, but we have not tested for that in 1.3
// In Repos Web 1.4 remove this
if (isset($_SERVER['REPOS_REPO']) && isset($_REQUEST['base'])) {
	//header('Location: '.preg_replace('/([?&])base=\w*/', '$1', $_SERVER['REQUEST_URI'], 1)); exit;
	unset($_REQUEST['base']); // should be a lot faster, and works as long as code does not read the query string directly
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
	if (isset($_SERVER['REPOS_REPO_PARENT'])) {
		$repo = $_SERVER['REPOS_REPO_PARENT'];
		if (strBegins($repo, '/')) $repo = getHost().$repo;
		return $repo;
	}
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
	if (isset($_SERVER['HTTPS'])) {
		return $_SERVER['HTTPS'] == 'on';
	}
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		return true;
	}
	return false;
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
		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			// TODO handle comma separated hosts https://httpd.apache.org/docs/2.4/mod/mod_proxy.html#x-headers
			// And should we expect Host header to be preserved? instead?
			return preg_replace('/https?:\/\/[^\/]+/', 'https://'.$_SERVER['HTTP_X_FORWARDED_HOST'], $url);
		}
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
	// We can't handle both http and SSL on non-standard port because we need to be able to switch protocol between internal use and asLink
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
 * @return the path to the current script without query string,
 *  with service rewrite this returns the path of the URL, not the php script
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
 * Pages may define a service name by setting a REPOS_SERVICE_NAME constant.
 */
function getService() {
	if (defined('REPOS_SERVICE_NAME')) {
		return REPOS_SERVICE_NAME;
	}
	$p = getSelfPath();
	// Self path is not accurate in the rewrite case, need to use the actual script path
	if (isRealUrl()) {
		$n = $_SERVER['SCRIPT_NAME'];
		if ($n == $_SERVER['SCRIPT_URL']) { // with isRealUrl()==true this is typical for fcgi from rweb-services
			$n = $_SERVER['SCRIPT_FILENAME'];
			$n = substr($n, strpos($n, '/repos-web/'));
		}
		$p = getParent($n);
	}
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
 * Note that error pages don't get the query string but may get HTTP Accept.
 * Redirect-after-post should not be used when this method returns true.
 * @return true if the current request is for contents, not a user page
 * @see ServiceRequest
 * @see isRequestInternal()
 */
function isRequestService() {
	if (isRequestNoBody()) return true;
	// Explicit service request using query/post parameter
	if (isset($_REQUEST[WEBSERVICE_KEY])) {
		return in_array($_REQUEST[WEBSERVICE_KEY], array('json','text','xml'));
	}
	// accept header, service if not html, for example return edit result as json
	if (isset($_SERVER["HTTP_ACCEPT"])
			// TODO we don't want false positives here so maybe we should parse properly and also consider */* to be possible browser request?
			// Until then, apply this new rule only for the new service URLs 
			&& isset($_REQUEST['rweb'])
			// ... problems with IE6 already, sends 
			&& (!strContains($_SERVER['HTTP_USER_AGENT'], 'MSIE') || !strContains($_SERVER["HTTP_ACCEPT"], '*/*'))
			) {
		if (!strBegins($_SERVER["HTTP_ACCEPT"], "text/html")) return true; // Expecting all browsers to send text/html,...
	}
	// This method may be used in CLI mode, in which case there are no service requests
	if (!isset($_SERVER['REQUEST_URI'])) return false;
	// ErrorDocument in repository might not get the proper superglobals
	if (preg_match('/serv=(json|text|xml)/', $_SERVER['REQUEST_URI'])) return true;
	return false;
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
 * @return true if the current page is delivered from a "real url", i.e. a servicelayer rewrite
 */
function isRealUrl() {
	// For rewrite with QSA we can use the rweb parameter, for servlet filter we have no other choice because the original URI is not known by Quercus
	return isset($_REQUEST['rweb']) ||
		// false positives is better than false negatives because realurl links tend to work from anywhere
		!strBegins($_SERVER['SCRIPT_NAME'], $_SERVER['SCRIPT_URL']);
}

/**
 * @return boolean true if the current request is running in embedded hosting, i.e. with Quercus limitations
 */
function isQuercus() {
	return strBegins($_SERVER['SERVER_SOFTWARE'], 'Apache PHP Quercus');
}

/**
 * @return boolean true if java integration is available
 */
function isReposJava() {
	return isQuercus();	
}

/**
 * For isReposJava() runtime.
 * Supports methods like getInfo(repositoryUrl, path).
 * If there's any statefulness or caching this method hides that from callers, who just invoke the methods.
 */
function getReposJavaBridge() {
	// using a static bridge to let rweb java handle the state
	return java_class('se.repos.rweb.php.ReposPhpBridge');
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

/**
 * Try to avoid https://issues.apache.org/jira/browse/SVN-2464 regardless of client platform
 */
function reposNormalizePath($path) {
	if (Normalizer::isNormalized($path)) return $path;
	return Normalizer::normalize($path);
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
