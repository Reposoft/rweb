<?php
/**
 * Repos logic shared by all PHP pages.
 *
 * Behaviour is configurable in the file
 * ../../repos-conf/repos.properties
 * 
 * Output (headers AND response) is controlled by either Report or Presentation.
 * 
 * @see Report
 * @see Presentation
 * @package conf
 */

$_repos_config = parse_ini_file( _getPropertiesFile(), false );

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
// TODO move to System class, togehter with other generic helper functions. this script should deal with configuration only.
if (!isset($_repos_config['repositories'])) trigger_error("No repositories configured");
if (!isset($_repos_config['repos_web'])) trigger_error("Repos web applicaiton root not specified in configuration");
if (!isFolder($_repos_config['repos_web'])) trigger_error("repos_web must be a folder (should end with '/')");
if (isset($_GET['file'])) trigger_error("The 'file' parameter is no longer supported");
if (isset($_GET['path'])) trigger_error("The 'path' parameter is no longer supported");
function _denyParam($name) { if (isset($_GET[$name]) || isset($_POST[$name])) trigger_error("The parameter '$name' is reserved.", E_USER_ERROR); }
_denyParam(REPO_KEY);
_denyParam(USERNAME_KEY);
_denyParam(LOCALE_KEY);
_denyParam(THEME_KEY);
//if (isset($_GET[REPO_KEY])) trigger_error()
if (get_magic_quotes_gpc()!=0) { trigger_error("The repos server must disable magic_quotes"); } // tested in server test

/**
 * @return true if the current request is internal, from a server page,
 * a web service client or an AJAX page
 * @see ServiceRequest
 */
function isRequestService() {
	return isset($_REQUEST[WEBSERVICE_KEY]) && $_REQUEST[WEBSERVICE_KEY]=='json';
	//return isRequestInternal();
}

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
 * Returns the URL to the root folder of the web application.
 * Can be a complete URL with host and path, as well as an absolute URL from server root.
 * 
 * @return String absolute url to the repos web application URL, ending with slash
 */
function getWebapp() {
	return _getConfig('repos_web');
}

/**
 * Static resources may be delivered from a different URL than dynamic webapp, for performance.
 * For example dynamic pages that require login might need SSL, but images don't.
 * If uncertain, use getWebapp instead of this function
 * 
 * @return String webapp root URL to static resources like images, ending with slash
 * @deprecated use getWebapp. If HTTPS is required for _all_ resources, getWebapp will return https,
 *  if not then redirection is done by the apache server
 */
function getWebappStatic() {
	if ($w = _getConfig('repos_static')) {
		return $w;
	} else {
		return getWebapp();
	}
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

// ----- string helper functions that should have been in php -----
function strBegins($str, $sub) { return (substr($str, 0, strlen($sub)) === $sub); }
function strEnds($str, $sub) { return (substr($str, strlen($str) - strlen($sub)) === $sub); }
function strContains($str, $sub) { return (strpos($str, $sub) !== false); }
function strAfter($str, $sub) { return (substr($str, strpos($str, $sub) + strlen($sub))); }

// ----- logic for the repos naming conventions for path -----

/**
 * A path is a String of any length, not containing '\'.
 * Windows paths must be normalized using toPath.
 */
function isPath($path) {
	if (!is_string($path)) {
		trigger_error("Path $path is not a string.", E_USER_ERROR);
		return false;
	}
	if (strContains($path, '\\')) {
		trigger_error("Path $path contains backslash. Use toPath(path) to convert to generic path.", E_USER_ERROR);
		return false;
	}
	if (strContains(str_replace('://','',$path), '//')) {
		trigger_error("Path $path contains double slashes.", E_USER_ERROR);
		return false;
	}
	return true;
}

/**
 * Converts filesystem path to path that works on all OSes, with same encoding as command line.
 * Windows paths are converted from backslashes to forward slashes, and from UTF-8 to ISO-8859-1 (see toShellEncoding).
 * If a path does not use the OS encoding, functions like file_exists will only work with ASCII file names. 
 * @param String $path path that might contain backslashes
 * @return String the same path, but with forward slashes
 */
function toPath($path) {
	return toShellEncoding(strtr($path, '\\', '/'));
}

/**
 * Absolute paths start with '/' or 'protocol://', on Windows only, 'X:/'.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is absolute, false if not
 */
function isAbsolute($path) {
	if (!isPath($path)) trigger_error("'$path' is not a valid path", E_USER_ERROR);
	if (strBegins($path, '/')) return true;
	if (isWindows() && ereg('^[a-zA-Z]:/', $path)) return true;
	return ereg('^[a-z]+://', $path)!=false;
}

/**
 * Relative paths are those that are not absolute, including empty strings.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is relative, false if not 
 */
function isRelative($path) {
	return !isAbsolute($path);
}

/**
 * Files are relative or absolute paths that do not end with '/'.
 * The actual filename can be retreived using getParent($path).
 * @param String $path the file system path or URL to check
 * @return boolean true if path is a file, false if not 
 */
function isFile($path) {
	if (!isPath($path)) trigger_error("'$path' is not a valid path");
	return !strEnds($path, '/');
}

/**
 * Folders are relative or absolute paths that _do_ end with '/'
 *  or (on Windows only) '\'
 * To check if a URL with no tailing slash is a folder, use login_getResourceType.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is a folder, false if not 
 */
function isFolder($path) {
	return !isFile($path);
}

/**
 * @param String $path the file system path or URL to check
 * @return The parent folder if isFolder($path), the folder if isFile($path), false if there is no parent
 */
function getParent($path) {
	if (strlen($path)<1) return false;
	$c = substr_count($path, '/');
	if ($c < 2 || ($c < 4 && strContains($path, '://') && !($c==3 && !strEnds($path, '/')))) return false; 
	$f = substr($path, 0, strrpos(rtrim($path,'/'), '/'));
	if (strlen($f)==0 && isRelative($path)) return $f;
	return $f.'/';
}

// ----- helper functions for pages to refer to internal urls -----

/**
 * Root of the repos application
 * @deprecated use getWebapp() instead
 */
function repos_getWebappRoot() {
	trigger_error("use getWebapp() instead of repos_getWebappRoot()");
	return getWebapp();
}

/**
 * @return The url to the host of this request, 
 * without tailing slash because absolute urls should be appended.
 */
function repos_getSelfRoot() {
	$url = 'http';
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') $url .= 's';
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
 * Complete url = repos_getSelfUrl().'?'.repos_getSelfQuery();
 */
function repos_getSelfUrl() {
	$uri = $_SERVER['REQUEST_URI'];
	$q = strpos($uri, '?');
	if ($q > 0) {
		$uri = substr($uri, 0, $q);
	}
	// $_SERVER['SCRIPT_NAME'] can not be used because it always contains the filename
	return repos_getSelfRoot() . $uri;
}

/**
 * Current query string, or empty string if there is no query string
 * @return string
 */
function repos_getSelfQuery() {
	if (!isset($_SERVER['QUERY_STRING'])) return '';
	return $_SERVER['QUERY_STRING'];
}

/**
 * 
 * @return boolean true if the current client is local REMOTE_ADDR
 */
function isRequestLocal() {
	return isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']=='127.0.0.1';
}

/**
 * This web application runs an intenal HTTP client that is needed to check status codes of pages
 * @return boolean true if the current request is from our own HTTP client
 */
function isRequestInternal() {
	if (isRequestLocal()) { // assumes we're not mirrored and don't use a proxy
		// this is not proper identification, but at least it does not return true for any normal browser
		return !isset($_SERVER['HTTP_USER_AGENT']);
	}
	return false;
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
	return strBegins($url, getRepository());
}

// ----- file system helper functions ------

// it is not allowed for code outside this file to do
// any of: unlink(x), mkdir(x), touch(x), fopen(x, 'a' or 'w')
//
// instead, use the create and delete functions below

/**
 * Platform independen way of getting the server's temp folder.
 * @return String absolute path, folder, existing
 * @deprecated use System::getApplicationTemp instead, this functionality should be in System
 */
function getSystemTempDir() {
	$type = '';
	if (getenv('TMP')) {
		$type = 'TMP';
		$tempdir = getenv('TMP');
	} elseif (getenv('TMPDIR')) {
		$type = 'TMPDIR';
		$tempdir = getenv('TMPDIR');
	} elseif (getenv('TEMP')) {
		$type = 'TEMP';
		$tempdir = getenv('TEMP');
	} else {
		$type = 'tempnam';
		// suggest a directory that does not exist, so that tempnam uses system temp dir
		$doesnotexist = 'dontexist'.rand();
		$tmpfile = tempnam($doesnotexist, 'emptytempfile');
		if (strpos($tmpfile, $doesnotexist)!==false) trigger_error("Could not get system temp, got: ".$tmpfile);
		$tempdir = dirname($tmpfile);
		unlink($tmpfile);
		if (strlen($tempdir)<4) trigger_error("Attempted to use tempnam() to get system temp dir, but the result is: $tempdir", E_USER_ERROR);
	}

	if (empty($tempdir)) { trigger_error('Can not get the system temp dir', E_USER_ERROR); }
	
	$tempdir = rtrim(toPath($tempdir),'/').'/';
	if (strlen($tempdir) < 4) { trigger_error('Can not get the system temp dir, "'.$tempdir.'" is too short. Method: '.$type, E_USER_ERROR); }
	
	return $tempdir;
}

/**
 * Manages the common temp dir for repos-php. Temp is organized in subfolders per operation.
 * This method returns an existing temp folder; to get a new empty folder use getTempnamDir.
 * @param subdir optional category within the temp folder, no slashes
 * @return absolute path to the temp dir, ending with slash
 * @deprecated use System::getApplicationTemp instead
 */
function getTempDir($subdir=null) {
	// Get temporary directory
	$systemp = getSystemTempDir();
	
	// Make sure temporary directory is writable
	if (is_writable($systemp) == false) {
		die ('Temporary directory isn\'t writable');
	}

	$appname = str_replace('%', '_', rawurlencode(trim(strAfter(getWebapp(), '://'), '/')));
	$tmpdir = $systemp . $appname;
	if (!file_exists($tmpdir)) {
		mkdir($tmpdir);
	}
	if ($subdir) {
		$tmpdir .= '/' . $subdir;
		if (!file_exists($tmpdir)) {
			mkdir($tmpdir);
		}
	}
	return toPath($tmpdir) . '/';
}

/**
 * Like PHP's tempname() but creates a folder instead
 * @param subdir optional parent dir name for the new dir, no slashes
 * @return a newly created folder, with tailing slash
 * @deprecated use System::getTempFolder() instead
 */
function getTempnamDir($subdir=null) {
       // Use PHP's tmpfile function to create a temporary
       // directory name. Delete the file and keep the name.
       $tempname = _getTempFile($subdir);
       $tempname = toPath($tempname);
       if (!$tempname)
               return false; // TODO trigger error

       if (!unlink($tempname))
               return false; // TODO trigger error

       // Create the temporary directory and returns its name.
       if (mkdir($tempname))
               return $tempname.'/';

       return false;
}

// for delegation from System and deprecated getTempnamDir
function _getTempFile($subdir=null) {
	return tempnam(rtrim(getTempDir($subdir),'/'), '');
}

/**
 * replaces custom-made recursive folder remove.
 * Removes the folder recursively if it is in one of the allowed locations,
 * such as the temp dir and the repos folder.
 * Note that the path should be encoded with the local shell encoding, see toPath.
 * @param String $folder absolute path, with tailing DIRECTORY_SEPARATOR like all folders
 * @deprecated use System::deleteFolder
 */
function deleteFolder($folder) {
	_authorizeFilesystemModify($folder);
	if (!isFolder($folder)) {
		trigger_error("Path \"$folder\" is not a folder.", E_USER_ERROR); return false;
	}
	
	if (!file_exists($folder) || !is_dir($folder)) {
		trigger_error("Path \"$folder\" does not exist.", E_USER_ERROR); return false;
	}
	if (!is_readable($folder)) {
		trigger_error("Path \"$folder\" is not readable.", E_USER_ERROR); return false;
	}
	if (!is_writable($folder) && !_chmodWritable($folder)) {
		trigger_error("Path \"$folder\" is not writable.", E_USER_ERROR); return false;
	}
	else {
		$handle = opendir($folder);
		while (false !== ($item = readdir($handle))) {
			if ($item != '.' && $item != '..') {
				$path = $folder.$item;
				if(is_dir($path)) {
					deleteFolder($path.'/');
				} else {
					deleteFile($path);
				}
			}
		}
		closedir($handle);
		if(!rmdir($folder)) {
			trigger_error("Could not remove folder \"$folder\".", E_USER_ERROR); return false;
		}
		return true;
	}
}

/**
 * replaces touch().
 * @deprecated use System::createFile
 */
function createFile($absolutePath) {
	_authorizeFilesystemModify($absolutePath);
	if (!isFile($absolutePath)) {
		trigger_error("Path \" $absolutePath\" is not a valid file name.", E_USER_ERROR); return false;
	}
	return touch($absolutePath);
}

/**
 * replaces mkdir().
 * @deprecated use System::createFolder
 */
function createFolder($absolutePath) {
	_authorizeFilesystemModify($absolutePath);
	if (!isFolder($absolutePath)) {
		trigger_error("Path \" $absolutePath\" is not a valid folder name.", E_USER_ERROR); return false;
	}
	return mkdir($absolutePath);
}

/**
 * replaces unlink().
 * @param String $file absolute path to file
 * @deprecated use System::deleteFile
 */
function deleteFile($file) {
	_authorizeFilesystemModify($file);
	if (!isFile($file)) {
		trigger_error("Path \" $file\" is not a file.", E_USER_ERROR); return false;
	}
	if (!file_exists($file)) {
		trigger_error("Path \" $file\" does not exist.", E_USER_ERROR); return false;
	}
	if (!is_readable($file)) {
		trigger_error("Path \" $file\" is not readable.", E_USER_ERROR); return false;
	}
	if (!is_writable($file) && !_chmodWritable($file)) {
		trigger_error("Path \" $file\" is not writable.", E_USER_ERROR); return false;
	}
	return unlink($file);
}

/**
 * Instead of createFile() and fopen+fwrite+fclose.
 * @deprecated use System::createFileWithContents
 */
function createFileWithContents($absolutePath, $contents, $convertToWindowsNewlineOnWindows=false, $overwrite=false) {
	if (!isFile($absolutePath)) {
		trigger_error("Path $absolutePath is not a file."); return false;
	}
	if (file_exists($absolutePath) && !$overwrite) {
		trigger_error("Path $absolutePath already exists. Delete it first."); return false;
	}
	if ($convertToWindowsNewlineOnWindows) {
		$file = fopen($absolutePath, 'wt');	
	} else {
		$file = fopen($absolutePath, 'w');
	}
	$b = fwrite($file, $contents);
	fclose($file);
	return $b;
}

/**
 * Replaces chmod 0777.
 * Only allowes chmodding of folders that are expected to be write protected, like .svn. 
 * @return false if it is not allowed to chmod the path writable
 */
function _chmodWritable($absolutePath) {
	if (strContains($absolutePath, '/.svn')) return chmod($absolutePath, 0777);
	if (strBegins($absolutePath, getSystemTempDir())) return chmod($absolutePath, 0777);
	return false;
}

/**
 * It is considered a serious system error if a modify path is invalid according to the internal rules.
 * Therefore we throw an error and do exit.
 */
function _authorizeFilesystemModify($path) {
	// test and demo repository
	if (getConfig('allow_reset')==1) {
		if (strBegins($path, getConfig('admin_folder'))) return true;
		if (strBegins($path, getConfig('backup_folder'))) return true;
		if (strBegins($path, getConfig('local_path'))) return true;
	}
	// generic rules
	if (!isAbsolute($path)) {
		trigger_error("Security error: local write not allowed in \"$path\". It is not absolute.");// exit;
	}
	if (strBegins($path, getSystemTempDir())) {
		return true;
	}
	if (strBegins($path, toPath(dirname(dirname(__FILE__))))) {
		return true;
	}
	if (strBegins($path, toPath(dirname(dirname(dirname(__FILE__))).'/repos-config/'))) {
		return true;
	}
	trigger_error("Security error: local write not allowed in \"$path\". It is not a temp or repos dir.");// exit;
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
	//trigger_error("urlEncodeNames is deprecated. Use escapeArgument for command, rawurlencode for query params and htmlspecialchars for presentation.");
	$parts = explode('/', $url);
	$i = 0;
	if (strContains($url, '://')) $i = 3; // don't escape protocol and host
	for ($i; $i < count($parts); $i++) {
		$parts[$i] = rawurlencode($parts[$i]);
	}
	$encoded = implode('/', $parts);
	return $encoded;
}

// ------ functions to keep scripts portable -----

/**
 * @return true if script is running on windows OS, false for anything else
 * @deprecated use System::isWindows
 */
function isWindows() {
	return ( substr(PHP_OS, 0, 3) == 'WIN' );
}

/**
 * Converts a string from internal encoding to the encoding used for file names and commands.
 * @param String $string the value with internal encoding (same as no encoding)
 * @return String the same value encoded as the OS expects it on the command line
 * @deprecated use System::toShellEncoding
 */
function toShellEncoding($string) {
	if (isWindows()) {
		return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
	}
	return $string;
}

/**
 * Get the execute path of the subversion command line tools used for repository administration
 * @param Command name, i.e. 'svnadmin'.
 * @return Command line command, false if the command shouldn't be needed in current OS. Error message starting with 'Error:' if command name is not supported.
 * @deprecated use System::getCommand
 */
function getCommand($command) {
	if ( ! defined('USRBIN') )
		define( 'USRBIN', "/usr/bin/" );
	switch($command) {
		case 'svn':
			return ( isWindows() ? 'svn' : USRBIN . 'svn' );
		case 'svnlook':
			return ( isWindows() ? 'svnlook' : USRBIN . 'svnlook' );
		case 'svnadmin':
			return ( isWindows() ? 'svnadmin' : USRBIN . 'svnadmin' );
		case 'gzip':
			return ( isWindows() ? false : USRBIN . 'gzip' );
		case 'gunzip':
			return ( isWindows() ? false : USRBIN . 'gunzip' );
		case 'whoami':
			return 'whoami';
		case 'env':
			return ( isWindows() ? 'set' : USRBIN . 'env' );
		case 'du':
			return ( isWindows() ? false : USRBIN . 'du' );
	}
	return false;
}

// ----- internal functions -----

function _getConfigFolder() {
	$d = dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR;
	if (file_exists($d)) return $d;
	// old location
	return dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'repos-config'.DIRECTORY_SEPARATOR;
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
