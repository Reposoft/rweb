<?php
/**
 * Repos properties as php variables. Should be accessed through getConfig('key').
 *
 * First tries to read ../../repos.properties
 * (the parent directory of the repos installation).
 * If that file is not found, it reads from the same foler
 * the repos.properties that is included in the distribution.
 *
 * Also contains some generic functions needed everywhere.
 */
 
// ----- global settings -----

_setGlobalSettings();
_checkSystem();

// pages that can be included from anywhere need to use __FILE__ to do their own includes
$repos_config = parse_ini_file( _getPropertiesFile(), false );

/**
 * Config value getter
 * @param key the configuration value key, as in repos.properties
 * @return the value corresponding to the specified key. False if key not defined.
 */ 
function getConfig($key) {
	global $repos_config;
	if (isset($repos_config[$key]))
		return ($repos_config[$key] );
	return false;
}

// ----- user settings -----

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

// same function as in head.js
function repos_getUserTheme($user = '') {
	if ($user=='') {
		$user = getReposUser();
	}
	if (empty($user)) return '';
	if ($user=='test'||$user=='annika'||$user=='arvid'||$user=='hanna') return '';
	return 'themes/pe/';
}

// ----- helper functions for pages to refer to internal urls -----

// repos_getSelfRoot(): Current server's root url, no tailing slash
function repos_getWebappRoot() {
	return getConfig('repos_web');
}


// repos_getSelfUrl(): The url that the browser used to get the current page, excluding query string
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

// Complete self url = repos_getSelfUrl().'?'.repos_getSelfQuery();
function repos_getSelfUrl() {
	$uri = $_SERVER['REQUEST_URI'];
	$q = strpos($uri, '?');
	if ($q > 0) {
		$uri = substr($uri, 0, $q);
	}
	// $_SERVER['SCRIPT_NAME'] can not be used because it always contains the filename
	return repos_getSelfRoot() . $uri;
}

function repos_getSelfQuery() {
	return $_SERVER['QUERY_STRING'];
}

// ----- file system helper functions ------

/**
 * Handles the common temp dir for repos-php
 * @param subdir optional category within the temp folder, no slashes
 * @return avsolute path to the temp dir, ending with slash or backslash
 */
function getTempDir($subdir=null) {
	$parent = '/tmp'; // don't know how to get PHPs system temp dir
	$appname = str_replace('%', '_', rawurlencode(substr(getConfig('repos_web'), 7)));
	$tmpdir = rtrim($parent, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $appname;
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
 * @return a newly created folder
 */
function getTempnamDir($subdir='') {
       // Use PHP's tmpfile function to create a temporary
       // directory name. Delete the file and keep the name.
       $tempname = tempnam(getTempDir($subdir), '');
       if (!$tempname)
               return false;

       if (!unlink($tempname))
               return false;

       // Create the temporary directory and returns its name.
       if (mkdir($tempname))
               return $tempname;

       return false;
}

/**
 * Removes a directory created using getTempDir or getTempnamDir
 * @param directory absolute path
 */
function removeTempDir($directory) {
	if (strpos($directory, getTempDir())===false) {
		trigger_error("Can not remove non-temp dir $dir."); exit;
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
					unlink($path);
				}
			}
		}
		closedir($handle);
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
	global $hasencoded; $hasencoded = true; // security check, set to true in the encoding functions and checked before 'run'
	$parts = explode('/', $url);
	// first part is the protocol, don't escape
	for ($i = 1; $i < count($parts); $i++) {
		$parts[$i] = rawurlencode($parts[$i]);
	}
	$encoded = implode('/', $parts);
	if ('"'.$encoded.'"' != escapeArgument($encoded)) { // doucle check. remove if it's not worth the clock cycles.
		trigger_error("Error. Could not safely encode the URL.");
	}
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
	// windows uses % to get variable names. URLs _should_ not be encoded, but if they are we might have a problem.
	if(isWindows()) {
		$arg = str_replace('%','°/.', $arg);
	}
	return '"'.$arg.'"';
	// #&;`|*?~<>^()[]{}$\, \x0A  and \xFF. ' and "
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

function _repos_getFullCommand($commandName, $argumentsString) {
	$run = getCommand($commandName);
	$wrapper = _repos_getScriptWrapper();
	if (strlen($wrapper)>0) {
		// make one argument (to the wrapper) of the entire command
		// the arguments in the argumentsString are already escaped and surrounded with quoutes where needed
		// existing single quotes must be adapted for shell
		$run = " '".$run.' '.str_replace("'","'\\''",$argumentsString).' 2>&1'."'";
	} else {
		$run += ' '.$argumentsString;
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
	return dirname(dirname(dirname(__FILE__))).'/reposrun.sh';
}

// ------ functions to keep scripts portable -----

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
	if ( isWindows() )
		return "\n\r";
	else return "\n";
}

/**
 * Make a path work on any operating system
 * @param pathWithSlashes for example /absolute/path or ../relative
 */
function getLocalPath($pathWithSlashes) {
    /* We don't know drive letter.
     * Instead, in windows, the user should install apache2 on the same drive as the config files.
	if ( isWindows() )
		return 'C:' . $pathWithSlashes;
	 */
	return $pathWithSlashes;
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

// ----- indernal functions -----

function _getPropertiesFile() {
	$propertiesFile = dirname(dirname(dirname(__FILE__))) . '/repos.properties';
	if (!file_exists($propertiesFile)) {
		$propertiesFile = dirname(__FILE__) . '/repos.properties';
	}
	return $propertiesFile;
}

function _setGlobalSettings() {
	// during development, show all errors to the user
	error_reporting(E_ALL);
	// cookie settings
	define('LOCALE_KEY', 'lang');
	define('THEME_KEY', 'theme');
	define('USERNAME_KEY', 'username');
}

function _checkSystem() {
	// assume that magic quotes is enabled
	if (get_magic_quotes_gpc()!=0) {
		trigger_error("This server does not have magic quotes disabled. Repos PHP does not work with magic quotes.");
	}
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
