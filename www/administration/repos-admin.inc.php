<?php

// include from the parent directory, offset 1 could be used instead of rtrim in php5
require( substr(dirname(__FILE__), 0, strrpos(rtrim(dirname(__FILE__),'/'),'/') ) . "/conf/repos.properties.php" );

// --- output functions ---
function start($title) {
	echo "<html><head><title>$title</title></head><body>\n";
	echo "<h1>$title</h1>\n";
}

// internal
function linestart($class='normal') {
	echo "<p class=\"$class\">";
}
function lineend() {
	echo "</p>\n";
}

// displaying output 
function debug($message) {
	linestart('debug');
	output($message);
	lineend();
}

function info($message) {
	linestart();
	output($message);
	lineend();
}

function warn($message) {
	linestart('warning');
	output($message);
	lineend();
}

function error($message) {
	linestart('error');
	output($message);
	lineend();
}

function fatal($message, $code = 1) {
	error( $message );
	done( $code );
}

function output($message) {
	if (is_array($message))
		$message = implode("<br />\n",$message);
	echo $message;
}

function done($code = 0) {
	echo "</body></html>\n\n";
	exit( $code );
}

// --- helper functions ---

/**
 * @return newline character for this OS
 */
function getNewLine() {
	return "\n\r";
}

/**
 * Get the execute path of the subversion command line tool
 * @param command Command name, i.e. 'svnadmin'. Optional. Defaults to 'svn'. 
 * @return command line command, false if internal function should be used
 */
function getCommand($command) {
	if ( ! defined('USRBIN') )
		define( 'USRBIN', "/usr/bin/" );
	switch($command) {
		case 'svn':
			return ( isWindows() ? 'svn' : USRBIN . 'svn' );
		case 'svnadmin':
			return ( isWindows() ? 'svnadmin' : USRBIN . 'svnadmin' );
		case 'gzip':
			return ( isWindows() ? false : USRBIN . 'gzip' );
		case 'gunzip':
			return ( isWindows() ? false : USRBIN . 'gunzip' );
		default:
			fatal("Command '$command' not supported");
	}
}

/**
 * Make a path work on any operating system
 * @param pathWithSlashes for example /absolute/path or ../relative
 */
function getLocalPath($pathWithSlashes) {
	if (isWindows())
		return 'C:' . $pathWithSlashes;
	return $pathWithSlashes;
}

/**
 * Get files and subdirectories in directory.
 * @param directory Path to check
 * @param startsWith Optional. Include only names that start with this prefix. 
 * @return Filenames as array sorted alpabetically
 */
function getDirContents($directory, $startsWith='') {
	if ( ! file_exists($directory) )
		warn( "Directory $directory does not exist" );
	$filelist = array();
	if ($dir = opendir($directory)) {
	   while (false !== ($file = readdir($dir))) 
		   if($file != ".." && $file != ".")
		   		if ( stristr($file,$startsWith)==$file )
					$filelist[] = $file;
	   closedir($dir);
	} else {
		warn( "Directory $directory couls not be opened" );
	}
	asort($filelist);
	return $filelist;
}

/**
 * Request input from user. Currently requires command line.
 */ 
function getUserInput($message='Provide input and press return:\n') {
	$stdin = fopen('php://stdin', 'r'); 
	echo "$message\n";
	$input = fgets($stdin,100); 
	fclose($stdin); 
	return $input;
}

// ----- unit tests ----
if ( isTestRun() ) {
	debug("---- Running unit tests ----");
	php repos-admin.inc.php unitTest

}

?>
