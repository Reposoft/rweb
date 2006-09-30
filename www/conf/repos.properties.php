<?php
/**
 * Repos logic shared by all PHP pages.
 *
 * Behaviour is configurable in the file
 * ../../repos-conf/repos.properties
 */

$_repos_config = parse_ini_file( _getPropertiesFile(), false );

// ----- global settings -----

error_reporting(E_ALL);
function reportError($n, $message, $file, $line) {
	$trace = _getStackTrace();
	if (function_exists('reportErrorToUser')) {
		call_user_func('reportErrorToUser', $n, $message, $trace); // may exit()
	} else if($n==E_USER_ERROR || $n==E_USER_WARNING || $n==E_USER_NOTICE) {
		echo("Error: $message\n<pre>\n$trace</pre>");
	}
}
set_error_handler('reportError');

// cookie settings
define('LOCALE_KEY', 'lang');
define('THEME_KEY', 'theme');
define('USERNAME_KEY', 'username');

// --- application selfcheck, can be removed in releases (tests should cover this) ---
if (!isset($_repos_config['repositories'])) trigger_error("No repositories configured");
if (!isset($_repos_config['repos_web'])) trigger_error("Repos web applicaiton root not specified in configuration");
if (isset($_GET['file'])) trigger_error("The 'file' parameter is no longer supported");
if (isset($_GET['path'])) trigger_error("The 'path' parameter is no longer supported");
if (get_magic_quotes_gpc()!=0) { trigger_error("The repos server must disable magic_quotes"); }

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
 * Repository is resolved using HTTP Referrer with fallback to settings.
 * To find out where root is, query paramter 'path' must be set.
 * @return Root url of the repository for this request, no tailing slash. Not encoded.
 */
function getRepository() {
	// 1: query string
	if (isset($_GET['repo'])) {
		return rtrim($_GET['repo'],'/');
	}
	// 2: referer without a query AND query string param 'target'
    $ref = getHttpReferer();
	if (strpos($ref, '?')===false && isset($_GET['target'])) {
		$path = rtrim(getPath(),'/');
		if ($ref && $path) {
			$repo = getRepoRoot($ref,$path);
			if ($repo) return $repo;
		}
	}
	// 3: fallback to default repository
    if(function_exists('getConfig')) {
    	return _getConfig('repo_url');
	}
	return false;
}

/**
 * Returns the URL to the root folder of the web application.
 * Can be a complete URL with host and path, as well as an absolute URL from server root.
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

// ----- string helper functions that should have been in php -----
function strBegins($str, $sub) { return (substr($str, 0, strlen($sub)) === $sub); }
function strEnds($str, $sub) { return (substr($str, strlen($str) - strlen($sub)) === $sub); }
function strContains($str, $sub) { return (strpos($str, $sub) !== false); }

// ----- user settings, maybe this should be in account instead -----

$possibleLocales = array(
	'sv' => 'Svenska',
	'en' => 'English',
	'de' => 'Deutsch'
	);
	
// locales might require setting a cookie, which requires headers,
//  which must be sent before anything else, 
//  so we run the function directly when the file is included
repos_getUserLocale();
	
/**
 * Resolve locale code from: 1: GET, 2: SESSION, 3: browser
 * @return two letter language code, lower case
 */
function repos_getUserLocale() {
	global $possibleLocales;
	$locale = 'en'; 
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $locale = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	if(array_key_exists(LOCALE_KEY,$_COOKIE)) $locale = $_COOKIE[LOCALE_KEY];
	if(array_key_exists(LOCALE_KEY,$_GET)) $locale = $_GET[LOCALE_KEY];
	// validate that the locale exists
	if( !isset($possibleLocales[$locale]) ) {
		$locale = array_shift(array_keys($possibleLocales));
	}
	// save and return
	if (!isset($_COOKIE[LOCALE_KEY])) {
		setcookie(LOCALE_KEY,$locale,0,'/');
	} else {
		$_COOKIE[LOCALE_KEY] = $locale;
	}
	return $locale;	
}

// ----- logic for the repos naming conventions for path -----

/**
 * A path is a String of any length, not containing '\'.
 * Windows paths must be normalized using toPath.
 */
function isPath($path) {
	if (strContains($path, '\\')) {
		trigger_error('Paths can not contain backslash. Use toPath(path) to convert to generic path.');
		return false;
	}
	return (is_string($path));
}

/**
 * Converts windows path to path that works on all OSes
 * @param String $path path that might contain backslashes
 * @return String the same path, but with forward slashes
 */
function toPath($path) {
	return strtr($path, '\\', '/');
}

/**
 * Absolute paths start with '/' or 'protocol://', on Windows only, 'X:/'.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is absolute, false if not
 */
function isAbsolute($path) {
	if (!isPath($path)) trigger_error("'$path' is not a valid path");
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
 * @param String $path the file system path or URL to check
 * @return boolean true if path is a folder, false if not 
 */
function isFolder($path) {
	return !isFile($path);
}

/**
 * @param String $path the file system path or URL to check
 * @return The parent folder if isFolder($path), the folder if isFile($path)
 */
function getParent($path) {
	if (!isPath($path)) trigger_error("'$path' is not a valid path");
	if (strlen($path)==0 || $path=='/' || (isWindows() && strlen($path)<4 && strpos($path, ':')==1)) {
		trigger_error("Parent is not defined for path '$path'");
		return;
	}
	$f = substr($path, 0, strrpos(rtrim($path,'/'), '/'));
	if (strlen($f)==0 && isRelative($path)) return $f;
	return $f.'/';
}

// ----- helper functions for pages to refer to internal urls -----

/**
 * Root of the repos application
 */
function repos_getWebappRoot() {
	return getConfig('repos_web');
}

/**
 * The url to the host of this request
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

// ----- file system helper functions ------

/**
 * Platform independen way of getting the server's temp folder
 */
function getSystemTempDir() {
	if (!empty($_ENV['TMP'])) {
		$tempdir = $_ENV['TMP'];
	} elseif (!empty($_ENV['TMPDIR'])) {
		$tempdir = $_ENV['TMPDIR'];
	} elseif (!empty($_ENV['TEMP'])) {
		$tempdir = $_ENV['TEMP'];
	} else {
		$tempdir = dirname(tempnam('', 'emptytempfile'));
	}

	if (empty($tempdir)) { die ('Can not get the system temp dir'); }
	
	return $tempdir;
}

/**
 * Handles the common temp dir for repos-php
 * @param subdir optional category within the temp folder, no slashes
 * @return absolute path to the temp dir, ending with slash or backslash
 */
function getTempDir($subdir=null) {
	// Get temporary directory
	$systemp = getSystemTempDir();
	
	// Make sure temporary directory is writable
	if (is_writable($systemp) == false) {
		die ('Temporary directory isn\'t writable');
	}

	$appname = str_replace('%', '_', rawurlencode(substr(getWebapp(), 7)));
	$tmpdir = rtrim($systemp, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $appname;
	if (!file_exists($tmpdir)) {
		mkdir($tmpdir);
	}
	if ($subdir) {
		$tmpdir .= DIRECTORY_SEPARATOR . $subdir;
		if (!file_exists($tmpdir)) {
			mkdir($tmpdir);
		}
	}
	return $tmpdir . DIRECTORY_SEPARATOR;
}

/**
 * Like PHP's tempname() but creates a folder instead
 * @param subdir optional parent dir name for the new dir, no slashes
 * @return a newly created folder, with tailing slash
 */
function getTempnamDir($subdir=null) {
       // Use PHP's tmpfile function to create a temporary
       // directory name. Delete the file and keep the name.
       $tempname = tempnam(getTempDir($subdir), '');
       if (!$tempname)
               return false;

       if (!unlink($tempname))
               return false;

       // Create the temporary directory and returns its name.
       if (mkdir($tempname))
               return $tempname.DIRECTORY_SEPARATOR;

       return false;
}

/**
 * Removes a directory created using getTempDir or getTempnamDir
 * @param directory absolute path
 */
function removeTempDir($directory) {
	if (strpos($directory, getTempDir())===false) {
		trigger_error("Will not remove non-temp dir $directory.");
		return;
	}
	rtrim($directory, DIRECTORY_SEPARATOR);
	if(!file_exists($directory) || !is_dir($directory)) {
		return false;
	} elseif(!is_readable($directory)) {
		return false;
	} else {
		$handle = opendir($directory);
		while (false !== ($item = readdir($handle))) {
			if ($item != '.' && $item != '..') {
				$path = $directory.'/'.$item;
				if(is_dir($path)) {
					removeTempDir($path);
				} else {
					chmod($path, 0777);
					unlink($path);
				}
			}
		}
		closedir($handle);
		chmod($directory, 0777);
		if(!rmdir($directory)) {
			return false;
		}
		return true;
	}
}

// ---- functions through which all command execution should go ---

$hasencoded = false; // security check, set to true in the encoding functions and checked before 'run'

// Make an url safe as both command argument and URL (full url starting with protocol://)
// This meathod is only suitable for URLs that refer to existing resources.
// If the URL is used for write operations, international characters such as umlauts will not be correct in the repository
function urlEncodeNames($url) {
	trigger_error("urlEncodeNames is deprecated. Use escapeArgument for command, rawurlencode for query params and htmlspecialchars for presentation.");
	global $hasencoded; $hasencoded = true; // security check, set to true in the encoding functions and checked before 'run'
	$parts = explode('/', $url);
	// first part is the protocol, don't escape
	for ($i = 1; $i < count($parts); $i++) {
		$parts[$i] = rawurlencode($parts[$i]);
	}
	$encoded = implode('/', $parts);
	return $encoded;
}

// Commands are the first element on the command line and can not be enclosed in quotes
function escapeCommand($command) {
	return escapeshellcmd($command);
}

// Encloses an argument in quotes and escapes any quotes within it
function escapeArgument($argument) {
	global $hasencoded; $hasencoded = true; // security check, set to true in the encoding functions and checked before 'run'
	// Shell metacharacters are: & ; ` ' \ " | * ? ~ < > ^ ( ) [ ] { } $ \n \r (WWW Security FAQ [Stein 1999, Q37])
	// Use escapeshellcmd to make argument safe for command line
	// (double qoutes around the string escapes: *, ?, ~, ', &, <, >, |, (, )
	$arg = preg_replace('/(\s+)/',' ',$argument);
	$arg = str_replace("\\","\\\\", $arg);
	$arg = str_replace("\x0A", " ", $arg);
	$arg = str_replace("\xFF", " ", $arg);
	$arg = str_replace('"','\"', $arg);
	$arg = str_replace('$','\$', $arg);
	$arg = str_replace('`','\`', $arg);
	// On SuSE ! is a metacharacter in strings
	$arg = str_replace('!','\!', $arg);
	// windows uses % to get variable names, which can be used to read system properties.
	if(isWindows() && strContains($arg, '%')) {
		$arg = escapeWindowsVariables($arg);
	}
	return '"'.$arg.'"'; // The quotes are very important because they escape many characters that are not escaped here
	// #&;`|*?~<>^()[]{}$\, \x0A  and \xFF. ' and "
}

// if windows sees %abc%, it checks if abc is an environment variables. \% prevents this but adds the backslash to the string.
function escapeWindowsVariables($arg) {
	//$arg = str_replace('%','#', $arg);
	if(substr_count($arg, '%')>1) {
		// this should be very rare, so it can be an expensive operation
		foreach($_ENV as $key => $value) {
			while($s = stristr($arg, "%$key%")) { // must ignore case
				$arg = substr($arg, 0, strlen($arg)-strlen($s)).'#'.substr($s,1);
			}
		}
	}
	return $arg;
}

/**
 * Executes a given comman on the command line.
 * This function does not deal with security. Everything must be properly escaped.
 * @param a command like 'whoami'
 * @param everything that should be after the blankspace following the command,
 * @returns stdout and stderr output from the command, one array element per row. 
 *   Last element is the return code (use array_pop to remove).
 */
function repos_runCommand($commandName, $argumentsString) {
 	global $hasencoded; if (!$hasencoded) { // security check, set to true in the encoding functions and checked before 'run'. Not real protection.
		trigger_error("Possible security risk. No argument has been encoded.");
		exit;
	}
	//echo (_repos_getFullCommand($commandName, $argumentsString)); exit;
	exec(_repos_getFullCommand($commandName, $argumentsString), $output, $returnvalue);
	$output[] = $returnvalue;
	return $output;
}

/**
 * Executes a given comman on the command line and does passthru on the output
 * @param a command like 'whoami'
 * @param everything that should be after the blankspace following the command
 * @returns the return code of the execution. Any messages have been passed through.
 */
function repos_passthruCommand($commandName, $argumentsString) {
 	global $hasencoded; if (!$hasencoded) { // security check, set to true in the encoding functions and checked before 'run'. Not real protection.
		trigger_error("Possible security risk. No argument has been encoded.");
		exit;
	}
	passthru(_repos_getFullCommand($commandName, $argumentsString), $returnvalue);
	return $returnvalue;
}

/**
 * Compiles the exact string to run on the command line
 */
function _repos_getFullCommand($commandName, $argumentsString) {
	$run = getCommand($commandName);
	$wrapper = _repos_getScriptWrapper();
	if (strlen($wrapper)>0) {
		// make one argument (to the wrapper) of the entire command
		// the arguments in the argumentsString are already escaped and surrounded with quoutes where needed
		// existing single quotes must be adapted for shell
		$run = " '".$run.' '.str_replace("'","'\\''",$argumentsString).' 2>&1'."'";
	} else {
		$run .= ' '.$argumentsString;
	}
	return "$wrapper$run 2>&1";
}

/**
 * Might be nessecary to run all commands through a script that sets up a proper execution environment
 * for example locale for subversion.
 * @return wrapper script name if needed, or empty string if not needed
 */
function _repos_getScriptWrapper() {
	if (isWindows()) {
		return '';
	}
	return _getConfigFolder().'reposrun.sh';
}

// ------ functions to keep scripts portable -----

/**
 * @return true if this is PHP running from a command line instead of a web server
 */
function isOffline() {
	// maybe there is some clever CLI detection, but this works too
	return !isset($_SERVER['REQUEST_URI']);
}

/**
 * @return true if script is running on windows OS, false for anything else
 */
function isWindows() {
	return ( substr(PHP_OS, 0, 3) == 'WIN' );
}

/**
 * @return newline character for this OS
 */
function getNewline() {
	if (isOffline() && isWindows()) return "\n\r";
	else return "\n";
}

/**
 * Get the execute path of the subversion command line tools used for repository administration
 * @param Command name, i.e. 'svnadmin'.
 * @return Command line command, false if the command shouldn't be needed in current OS. Error message starting with 'Error:' if command name is not supported.
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
	}
	return "\"Error: Repos does not support command '$command'\"";
}

// ----- internal functions -----

function _getConfigFolder() {
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
	for($i=2; $i<count($stack); $i++) { // skip this method call and reportError
		if (isset($stack[$i]["file"]) && $stack[$i]["line"]) {
	    	$o .= "{$stack[$i]["file"]}\t{$stack[$i]["line"]}\t{$stack[$i]["function"]}\n";
		}
	}
	return $o;
}

// ------ unit testing support -----

/**
 * @return true if scripts should run self-test
 * @deprecated use phpunit instead. remove when admin scripts no longer use this.
 */
function isTestRun() {
	global $argv;
	return ( (isset($argv[1]) && $argv[1]=='unitTest') || isset($_GET['unitTest']) );
}
?>
