<?php

// include from the parent directory, offset 1 could be used instead of rtrim in php5
require( substr(dirname(__FILE__), 0, strrpos(rtrim(strtr(dirname(__FILE__),'\\','/'),'/'),'/') ) . "/conf/repos.properties.php" );

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

$hasErrors = false; // error events are recorded
function error($message) {
	$hasErrors = true;
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
		$message = formatArray($message);
	echo $message;
}

function formatArray($message) {
	$msg = '';
	foreach ( $message as $key=>$val ) {
		if ( $val===false )
			$val = 0;
		if ( is_string($key) )
			$msg .= "$key: ";
		$msg .= "$val<br />\n";
	}
	return $msg;
}

function done($code = 0) {
	echo "</body></html>\n\n";
	exit( $code );
}

// --- helper functions ---

/**
 * @return valid filename representing an absolute repository path
 */
function getPrefix($repository) {
	if ( ':'==substr($repository,1,1) )
		$repository = substr($repository,2);
	return ( "svnrepo" . rtrim(strtr($repository, "/\\", "--"),"-") . "-" );
}

/**
 * @return complete filename for backup, except file extension
 */
function getFilename($prefix, $fromrev, $torev) {
	return $prefix . formatRev($fromrev) . "-to-" . formatRev($torev);
}

/**
 * @return revision number formatted as fixed length string
 */
function formatRev($number) {
	if ($number > 9000000)
		error("Rediculously high revision number $number");
	return sprintf("%07s",$number);
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
		warn( "Directory $directory could not be opened" );
	}
	asort($filelist);
	return $filelist;
}

/**
 * Extract revision info from the backup files in a directory
 * @param directory The directory to list
 * @param startsWith The files to examine
 * @return array with one entry for each file, each entry containing an array 0=>filename, 1=>start revision, 2=>end revision
 */
function getBackupInfo($files, $startsWith='') {
	return array_map( 'getRevisionInfo', $files, $startsWith);
}

/**
 * @return array(backup file's name, from revision, to revision)
 */
function getRevisionInfo($filename, $startsWith) {
	$rev = array();
	if ( 0==ereg( '[0]*([0-9][0-9]*)-to-[0]*([0-9][0-9]*).*', substr($filename,strlen($startsWith)), $rev) )
		fatal("Could not extract revision numbers from filename $filename assuming the given prefix $startsWith");
	$rev[0] = $startsWith . $rev[0];
	$rev[1] = (int) $rev[1];
	$rev[2] = (int) $rev[2];	
	return $rev;
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
	
	debug("---- testing: formatRev ----");
	assertEquals( "0000012", formatRev(12) );
	
	debug("---- testing: getPrefix ----");
	assertEquals( "svnrepo-path-to-repo-", getPrefix("/path/to/repo") );
	assertEquals( "svnrepo-path-to-repo-", getPrefix("E:\\path\\to\\repo") );
	
	debug("---- testing: getFilename ----");
	assertEquals( "svnrepo-foo-0000000-to-1234567", getFilename("svnrepo-foo-",0,1234567) );
	
	debug("---- testing: getRevisionInfo ----");
	$info = getRevisionInfo("svnrepo-foo-0000000-to-1234567.dump.gz","svnrepo-foo-");
	assertEquals( "svnrepo-foo-0000000-to-1234567.dump.gz", $info[0] );
	assertEquals( 0, $info[1] );
	assertEquals( 1234567, $info[2] );
		
	debug("---- testing: getConfig ----");
	$dir = getConfig('backup_folder');

	// debug("---- testing:  ----");
	
	global $hasErrors;
	if( $hasErrors )
		fatal("There were test errors");
	else
		info("All tests passed");
}

// {{{ Header
/*
 * -File    	$Id: Assertable.php,v 1.4 2004/04/03 16:00:11 purestorm Exp $
 * -License   LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright 2003, Manuel Holtgrewe
 * -Author    manuel holtgrewe, <purestorm at teforge dot org>
 */
// }}}

    // {{{ method assertEquals($expected, $result, $comment=null)
    /**
     * This method executes an assertion that the two first
     * parameters are of equal value and type.
     * An error message field will be registered for output
     * if the assertion fails.
     *
     * @param   mixed       Expected value in the comparison.
     * @param   mixed       Second value for the comparison.
     * @param   string      Comment/description to/of test.
     *
     */
    function assertEquals($expected, $result, $comment=null) {
        $serialize = false;
        if (is_int($expected) or is_bool($expected) or is_float($expected)
                or is_double($expected) or is_string($expected) or is_null($expected))
            $equal = $expected === $result;
        else {
            $equal = serialize($expected) === serialize($result);
            $serialize = true;
        }

        if (!$equal) {
            $error_report = array(
                    //"Success" => false,
                    //"isEqualsAssertion" => true,

                    //"isSimpleAssertion" => false,
                    //"isNoExceptionAssertion" => false,
                    //"isHeader" => false,

                    "Expected" => $serialize ? serialize($expected) : $expected,
                    "ExpectedType" => gettype($expected),
                    "Result" => $serialize ? serialize($result) : $result,
                    "ResultType" => gettype($result),
                    "Comment" => $comment,
                    );
            error($error_report);
        }
    }
    // }}}
    // {{{ method assertEqualsMultinlineString($expected, $result, $comment=null)
    /**
     * TODO: Complete
     * This method checks the assertion that the first parameter
     * and the second are equal. Both have to be strings. Additionally
     * it checks where the two strings differ.
     *
     * @param   string      Expected value for comparison
     * @param   string      Second value for comparison.
     * @param   string      Comment/description to/of test.
     *
     */
    function assertEqualsMultilineString($expected, $result, $comment=Null) {
        assertEquals($expected, $result, $comment);
    }
?>
