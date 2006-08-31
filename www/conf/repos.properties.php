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
 
// --- global settings ---
// during development, show all errors to the user
error_reporting(E_ALL);
// assume that magic quotes is enabled
if (get_magic_quotes_gpc()!=1) {
	trigger_error("This server does not have magic quotes enabled. Repos PHP requires that.");
}

// pages that can be included from anywhere need to use __FILE__ to do their own includes
$propertiesFile = dirname(dirname(dirname(__FILE__))) . '/repos.properties';
if (!file_exists($propertiesFile)) {
	$propertiesFile = dirname(__FILE__) . '/repos.properties';
}
$repos_config = parse_ini_file( $propertiesFile, false );

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

/**
 * Handles the common temp dir for repos-php
 * @param subdir optional category within the temp folder, no slashes
 * @return avsolute path to the temp dir, ending with slash or backslash
 */
function getTempDir($subdir=null) {
	$parent = '/tmp'; // don't know how to get PHPs system temp dir
	$tmpdir = rtrim($parent, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'repos-php';
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

// --- helper functions for pages to refer to internal urls ---

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

// ---- functions through which all command execution should go ---

/**
 * Executes a given comman on the command line.
 * This function does not deal with security. Everything must be properly escaped.
 * @param a command like 'whoami'
 * @param everything that should be after the blankspace following the command,
 * @returns stdout and stderr output from the command, one array element per row. 
 *   Last element is the return code (use array_pop to remove).
 */
function repos_runCommand($commandName, $argumentsString) {
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
	passthru(_repos_getFullCommand($commandName, $argumentsString), $returnvalue);
	return $returnvalue;
}

function _repos_getFullCommand($commandName, $argumentsString) {
	$run = getCommand($commandName);
	$wrapper = _repos_getScriptWrapper();
	if (strlen($wrapper)>0) {
		// make one argument (to the wrapper) of the entire command
		// using magic_quotes_gpc, existing single quotes are already escaped, but they must be adapted for shell
		$run = " '".$run.' '.str_replace("\\'","'\\''",$argumentsString).' 2>&1'."'";
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
