<?php
/************** repos.se PHP authentication *****************
 * Compatible with java webapp and Apache2 mod_dav_svn access
 *	- Basic authentication
 *  - Enforces HTTPS protocol
 *
 * All scripts working with contents are expected to include
 * this file. Other scripts can use repos.properties.php.
 *
 * Designed for transparent login. Does not print any HTML.
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
 *  - askForCredentialsAndExit($realm);
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
 *  - getRepositoryUrl() repository root URL from query params,
 *     with fallback to repos.properties
 * Note that internally, URLs should never be urlencoded
 *
 * Nomenclature throughout the repos PHP solution:
 * 'path' absolute directory path from repository root
 * 'file' filename
 * 'target' absolute url from repository root to target resource
 * 'targeturl' URI of the resource, permanent location as an HTTP url
 * 'repo' repository root URI, uniquely defines a repository 
 */
require_once(dirname(dirname(__FILE__)) . '/conf/repos.properties.php');

// admin account identification (used for example to add extra post-commit operation in upload)
define('ADMIN_ACCOUNT', 'administrator');
// the versioned access control files (copied to the location given by repos.properties when changed)
define('ACCOUNTS_FILE', ADMIN_ACCOUNT.'/trunk/admin/repos-users');
define('ACCESS_FILE', ADMIN_ACCOUNT.'/trunk/admin/repos-access');

// Headers to disable caching, assumed to be needed on all pages that deal with contents
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0
header("Pragma: no-cache");

/**
 * Redirect to secure URL if current URL is not secure.
 * This should always be done for BASIC authentication.
 * This is skipped if query param SKIP_SL is set to one 1
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
	if (isLoggedIn()) {
		if(verifyLogin($targetUrl)) {
			// Do nothing. Parent script resumes operation.
		} else {
			header('HTTP/1.0 401 Unauthorized');
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
 * @return true if the current user can access the resource, 
 * TODO this means that 404 status returns true, make a function that returns the status code, and do error handling on unexpected codes
 */
function verifyLogin($targetUrl) {
	$user = getReposUser();
	if (!$user) {
		return false;
	}
	$headers = getHttpHeaders($targetUrl, $user, getReposPass());
	return strpos($headers[0], '401') || strpos($headers[0], '403') === false;
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
 * @param targetUrl The absolute URI of the resource
 * @return realm (string) authentication realm or false if login not required
 */
function getAuthName($targetUrl) {
	$conf = getConfig('repo_realm');
	if ($conf && strpos($targetUrl, $conf)==0) {
		return $conf;
	}
	return login_getAuthNameFromRepository($targetUrl);
}

// Uses HTTP to check authentication headers for a resource.
function login_getAuthNameFromRepository($targetUrl) {
	$headers = getHttpHeaders($targetUrl);
	//print_r($headers);
	if (strpos($headers[0], '401') == false) {
		return false;
	}
	$auth = $headers['WWW-Authenticate'];
	if(ereg('realm="([^"]*)"', $auth, $regs)) {
		return $regs[1];
	}
	trigger_error("Repos error: realm not found in authentication string: $auth");
	exit;
}

// abstraction for HTTP operation
function getHttpHeaders($targetUrl, $user=null, $pass=null) {
	return my_get_headers($targetUrl, $user, $pass);
}

// abstraction for referer resolution
function getReferer() {
    if (isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) > 5) {
		return $_SERVER['HTTP_REFERER'];
	}
    return false;
}

// ----- resource URL retreival functionality -----

/**
 * Decodes query string parameters that may be cleartext paths (as in svnindex)
 * @param array the server variable, for example $_GET
 * @param name the parameter name, for example 'path' as in ?path=/here/now/
 * Returns the non-escaped URL or path or filename or whatever
 */
function login_decodeQueryParam($array, $name) {
	return rawurldecode($array[$name]);
}

/**
 * @return path from repository root, ending with '/', non alphanumerical characters except '/' encoded
 */
function getPath() {
	if(!isset($_GET['path'])) return false;
	$path = login_decodeQueryParam($_GET,'path');
    $path = rtrim($path,'/').'/';
	return $path;
}

/**
 * @return filename if defined, false if the target is not a file
 */
function getFile() {
	if (isset($_GET['file'])) {
		return login_decodeQueryParam($_GET,'file');
	}
	// check if target has been set explicitly (this is in close cooperation with getTarget so we don't get a loop)
	if (isset($_GET['target'])) {
		$file = basename(login_decodeQueryParam($_GET,'target'));
		if (strlen($file) > 0) {
			return $file;
		}
	}
	return false;
}

/**
 * @return true if the target is a file, false if it is a folder or undefined
 */
function isTargetFile() {
	return (getFile()!=false);
}

/**
 * Target is the absolute url of a repository resource, from repository root
 * (thus starting with '/')
 * @return target in repository from query paramters WITH tailing slash if it is a directory, false if none of 'target', 'file' and 'path' is defined
 */
function getTarget() {
	if(isset($_GET['target'])) {
		return login_decodeQueryParam($_GET,'target');
	}
    // append filename if specified
    if(getFile()) return getPath() . getFile();
    return getPath();
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
	trigger_error("Error. Revision number '$rev' is not valid.");
	exit;
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
	// 2: referer without a query AND query string param 'path'
    $ref = getReferer();
	if (strpos($ref, '?')===false) {
		$path = rtrim(getPath(),'/');
		if ($ref && $path) {
			$repo = getRepoRoot($ref,$path);
			if ($repo) return $repo;
		}
	}
	// 3: fallback to default repository
    if(function_exists('getConfig')) {
    	return getConfig('repo_url');
	}
	return false;
}

/**
 * @return repository url (to root) with no tailing slash.
 *   Returns false if url is empty or if path is not part of url. 
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
 * @return logged in username, urlencoded, or false if no user (but use isLoggedIn to check)
 */
function getReposUser() {
	if (!isLoggedIn()) return false;
	// the username is used in urls (like the repository home dir for the user)
	//  so it is urlencoded to prevent urlinjection
	return urlencode($_SERVER['PHP_AUTH_USER']);
}
function getReposPass() {
	return $_SERVER['PHP_AUTH_PW'];
}
function getReposAuth() {
	return "This function is deprecated. Will be removed in 1.0"; // $_SERVER['HTTP_AUTHORIZATION'];
}

// *** Subversion client usage ***
define('SVN_CONFIG_DIR', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'svn-config-dir');

/**
 * Execute svn command like the PHP exec() function
 * @param cmd The command without the SVN part, for example 'log /url/to/repo'
 * @return stdout and stderr output from the command, one array element per row. 
 *   Last element is the return code (use array_pop to remove).
 *   If returnval!=0, login_handleSvnError may be used to get error message compatible with the styleshett.
 */
function login_svnRun($cmd) {
	$operation = escapeshellcmd($cmd);
	$svnCommand = login_getSvnSwitches().' '.$operation;
	$result = repos_runCommand('svn', $svnCommand);
	return $result;
}

/**
 * Execute svn command like the PHP passthru() function.
 * This can not be used from a Smarty template, because it returns an integer that will be appended to the page.
 * @param cmd The command without the SVN part, for example 'log /url/to/repo'
 * @return return value of the execution.
 *   If returnval!=0, login_handleSvnError may be used to get error message compatible with the styleshett.
 */
function login_svnPassthru($cmd) {
	$operation = escapeshellcmd($cmd);
	$svnCommand = login_getSvnSwitches().' '.$operation;
	$returnval = repos_passthruCommand('svn', $svnCommand);
	return $returnval;
}

/**
 * Cat file(@revision) directly to result stream.
 * Currently there's no error handling on error. Ideally error handling should be compatible with XML, XHTML and <pre>-tags.
 * This function can be used from a Smarty template like: {=$targetpeg|login_svnPassthruFile}
 * @param targetUrl the resource url. must be a file, can be with a peg revision (url@revision)
 * @param revision optional, >0, the revision to read. if omitted the function reads HEAD.
 */
function login_svnPassthruFile($targetUrl, $revision=0) {
	if ($revision > 0) {
		$cmd = 'cat '.escapeArgument($targetUrl.'@'.$revision); // using "peg" revision
	} else {
		$cmd = 'cat '.escapeArgument($targetUrl);
	}
	$returnvalue = login_svnPassthru($cmd);
	// can not return a value, because this function is often used to print directly to result //return $returnvalue;
	// TODO error handling
}

// TODO login_svnPassthruFileHtml

/**
 * Returns the mime type for a file in the repository.
 * If the property svn:mime-type is set, that value is returned. If not, default based on filename extension is returned.
 * @param targetUrl the file, might be with a peg revision
 * @return the mime type string
 */
function login_getMimeType($targetUrl) {
	$cmd = 'propget svn:mime-type '.escapeArgument($targetUrl);
	$result = login_svnRun($cmd);
	$returnvalue = array_pop($result);
	if ($returnvalue) {
		trigger_error("Could not find the file '$targetUrl' in repository version $revision." );
	}
	if (count($result) == 0) { // mime type property not set, return default
		return false;//deprecated in PHP//return mime_content_type(basename($targetUrl)); // svn:mime-type not set
		// return "application/x-unknown" ?
	}
	return $result[0];
}

/**
 * @return Mandatory arguments to the svn command
 */
function login_getSvnSwitches() {
	$auth = '--username='.getReposUser().' --password='.getReposPass().' --no-auth-cache';
	$options = '--non-interactive --config-dir '.SVN_CONFIG_DIR;
	return $auth.' '.$options;
}

/**
 * Renders error message as XML if SVN command fails. Use trigger_error for normal error message.
 * @TODO output as XHTML tags, so that the output can be used both in XSL and HTML pages
 */
function login_handleSvnError($executedcmd, $errorcode, $output = Array()) {
	echo "<error code=\"$errorcode\">\n";
	if (isset($_GET['DEBUG'])) {
		echo '<exec cmd="'.strtr($executedcmd,'"',"'").'"/>';
	}
	if (is_array($output)) {
		foreach ($output as $row) {
			echo '<output line="'.$row.'"/>';
		}
	}
	echo "</error>\n";
}

/**
 * @return true if the server's PHP installation has the SSL extension
 */
function login_isSSLSupported() {
	return function_exists('openssl_open');
}

// PHP5 get_headers function, but with authentication option
// currently supports only basic auth
function my_get_headers($url, $httpUsername, $httpPassword) {
   $url_info=parse_url($url);
   if (isset($url_info['scheme']) && $url_info['scheme'] == 'https') {
   	if (!login_isSSLSupported()) {
		trigger_error("Repos error: $url is a secure URL but this server does not have OpenSSL support in PHP.");
		exit;
	}
	   $port = 443;
	   @$fp=fsockopen('ssl://'.$url_info['host'], $port, $errno, $errstr, 10);
   } else {
	   $port = isset($url_info['port']) ? $url_info['port'] : 80;
	   @$fp=fsockopen($url_info['host'], $port, $errno, $errstr, 10);
   }
   if($fp) {
	   stream_set_timeout($fp, 10);
	   $head = "HEAD ".@$url_info['path']."?".@$url_info['query'];
	   $head .= " HTTP/1.0\r\nHost: ".@$url_info['host']."\r\n";
	   if (strlen($httpUsername) > 0) {
		$authString = 'Authorization: Basic '.base64_encode("$httpUsername:$httpPassword");
		$head .= $authString."\r\n";
	   }
	   $head .= "\r\n";
	   fputs($fp, $head);
	   //echo("----- http headers sent -----\n$head\n-------------------------\n");
	   while(!feof($fp)) {
		   if($header=trim(fgets($fp, 1024))) {
				   $sc_pos = strpos( $header, ':' );
				   if( $sc_pos === false ) {
					   $headers[0] = $header;
				   } else {
					   $label = substr( $header, 0, $sc_pos );
					   $value = substr( $header, $sc_pos+1 );
					   $headers[$label] = trim($value);
				   }
		   }
	   }
	   if (count($headers) < 1) {
	   	trigger_error("Repos error: could not get authentication requirements from $url");
		exit;
	   }
	   return $headers;
   }
   else {
   	trigger_error("Repos error: could not connect to target $url");
	exit;
   }
}

// set a cookie to tell javascripts which user this is
// this is set in cleartext under the USERNAME_KEY because it is never used for authentication
function login_setUsernameCookie() {
	$user = getReposUser();
	if (empty($user)) {
		trigger_error('User not logged in. Can not store username.');
	}
	setcookie(USERNAME_KEY, $user, 0, '/');
}

function login_clearUsernameCookie() {
	setcookie(USERNAME_KEY, '', time()-1, '/');
}

// automatic login if a target is specified the standard way
if (getTargetUrl()) {
	targetLogin();
}

?>
