<?php
/************** repos.se PHP authentication *****************
 * Compatible with java webapp and Apache2 mod_dav_svn access
 *	- Basic authentication
 *  - Enforces HTTPS protocol
 *  - Realm name = repository root url with no tailing slash
 *
 * Defines functions to retrieve credentials:
 *  - getReposUser() username
 *  - getReposPass() cleartext password for repository access
 *  - getReposAuth() encrypted authentication from browser
 *
 * Returns a 401 header if required credentials are not found
 *
 * Also provides functions for shared request processing
 *  - getReferer()   calling page
 *  - getTarget()    in-repository path for this operation
 *  - getRepositoryUrl() repository root and also realm name
 *  - getTargetUrl() full url to resource if specified
 *
 * If conf/repos.properties.php has been included before this
 * script is called, then fallback to settings is enabled.
 */
 
// *** Enforce HTTPS ***
if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) {
} else {
	$secureUrl = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	header("Location: $secureUrl");
	exit;
}

// *** Headers to disable caching ***
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0
header("Pragma: no-cache");

// *** url resolution functions, based on query parameters ***

function getReferer() {
    if (isset($_SERVER['HTTP_REFERER'])) return $_SERVER['HTTP_REFERER'];
    return false;
}

/**
 * @return target in repository from query paramters WITH tailing slash if not a file
 */
function getTarget() {
    // invalid parameters -> empty string
    if(!isset($_GET['path'])) return '';
    $path = $_GET['path'];
    // end with slash
    $path = rtrim($path,'/').'/';
    // append filename if specified
    if(isset($_GET['file'])) $path .= $_GET['file'];
    return $path;
}

/**
 * Repository is resolved using HTTP Referrer with fallback to settings.
 * To find out where root is, query paramter 'path' must be set.
 * @return Root url of the repository for this request
 */
function getRepositoryUrl() {
	if (isset($_GET['repo'])) {
		return $_GET['repo'];
	}
    $ref = getReferer();
    if ($ref && isset($_GET['path'])) {
		$repo = substr($ref,0,strpos($ref,$_GET['path']));
		if (count($repo)>0) return $repo; 
    }
    if(function_exists('getConfig')) {
    	return getConfig('repo_url');
	} else {
		return false;	
	}
}

/**
 * Target url is resolved from query parameters
 *  'repo' (fallback to getRepositoryUrl) = root url
 *  'path' = path from repository root
 *  'file' (omitted if not found) = filename inside path
 * @return Full url of the file or directory this request targets
 */
function getTargetUrl() {
    return getRepositoryUrl() . getTarget();
}


// *** Authentication ***

if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER']=='void') {
   header('WWW-Authenticate: Basic realm="' . getRepositoryUrl() . '"');
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
$repos_authentication;
$repos_authentication['user'] = $_SERVER['PHP_AUTH_USER'];
$repos_authentication['pass'] = $_SERVER['PHP_AUTH_PW'];
$repos_authentication['auth'] = substr($_SERVER['HTTP_AUTHORIZATION'], 6);

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
?>