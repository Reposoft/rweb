<?php
error_reporting(E_ALL);
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
 * @param subdir (optional) subdir, will be created if it does not exist, no slashes
 * @return avsolute path to the temp dir, ending with slash or backslash
 */
function getTempDir($subdir='') {
	$parent = '/tmp';
	$tmpdir = rtrim($parent, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'repos-php';
	if (!file_exists($tmpdir)) {
		mkdir($tmpdir);
	}
	$tmpdir .= DIRECTORY_SEPARATOR . $subdir;
	if (!file_exists($tmpdir)) {
		mkdir($tmpdir);
	}
	return $tmpdir . DIRECTORY_SEPARATOR;
}

/**
 * @return the url that the browser used to get the current page,
 *  _excluding_ query string
 */
function getSelfUrl() {
	$url = 'http';
	if($_SERVER['SERVER_PORT']==443) $url .= 's';
	$url .= '://' . $_SERVER['SERVER_NAME'];
	if($_SERVER['SERVER_PORT']==80 || $_SERVER['SERVER_PORT']==443) {
		// do not append port number
	} else {
		$url .= ':'.$_SERVER['SERVER_PORT'];
	}
	$rq = parse_url($_SERVER['REQUEST_URI']);
	$url .= $rq['path']; // $_SERVER['SCRIPT_NAME'] can not be used because it always contains the filename;
	return $url;
}

/**
 * @return the url that the browser used to get the current page,
 *  including query string
 */
function getSelfUrlAndQuery() {
	return getSelfUrl() . '?' . $_SERVER['QUERY_STRING'];
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
	}
	return "\"Error: Repos does not support command '$command'\"";
}

// ------ unit testing support -----

/**
 * @return true if scripts should run self-test
 */
function isTestRun() {
	global $argv;
	return ( (isset($argv[1]) && $argv[1]=='unitTest') || isset($_GET['unitTest']) );
}
?>
