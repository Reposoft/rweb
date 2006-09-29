<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require( upOne(dirname(__FILE__)) . "/conf/repos.properties.php" );

/**
 * This file contains helper functions for backup administration (such as file name resolution).
 * It also contains a small library to make HTML output from a batch job.
 */

// --- output functions ---

// override theme function
function getTheme() {
	return "/themes/simple"; 
}

// displaying output 
// TODO Report.class.php
// TODO function ok($message)
// TODO Report.publish
// TODO Report.display (like smarty display)
// TODO count X output lines (info+), X warnings, X fails, X exception
// TODO count fatal() as exception
// TODO CSS class for pre tags
// TODO make function names not html-specific. page-start, page-end
class Report {

	var $hasErrors = false; // error events are recorded
	var $offline;

	function Report($title='Repos system report', $category='') {
		$this->offline = isOffline();
		if ($this->offline) {
			$this->linestart();
			$this->output("--- $title ---");
			$this->lineend();
		} else {
			$this->_pageStart($title);
		}
	}
	
	/**
	 * Call when a test or validation has completed successfuly
	 * (opposite to error)
	 */
	function ok($message) {
		$this->linestart('ok');
		$this->output($message);
		$this->lineend();
	}
	
	/**
	 * Completes the report and saves it as a file at the default reports location.
	 */
	function publish() {
		trigger_error("publish() not implemented");
		$this->display();
	}
	
	/**
	 * Ends output and writes it to output stream.
	 */
	function display() {
		$this->_pageEnd();
	}
	
function _pageStart($title) {
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"';
	echo ' "http://www.w3.org/TR/html4/loose.dtd">';
	echo "\n<html>";
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
	echo '<title>Repos administration: ' . $title . '</title>';
	echo '<link href="/repos/style/global.css" rel="stylesheet" type="text/css">';
	?>
<script>
function hide(level) {
	var p = document.getElementsByTagName('p');
	for (i = 0; i < p.length; i++) {
		if (p[i].getAttribute('class') == level) p[i].style.display = 'none';
	}
}
function showAll() {
	var p = document.getElementsByTagName('p');
	for (i = 0; i < p.length; i++) {
		p[i].style.display = '';
	}	
}
</script>
	<?php
	echo "</head>\n";
	echo "<body onLoad=\"hide('debug')\">\n";
	echo "<p><a href=\"javascript:showAll()\">Show also debug level</a></p>";
}

// internal
function linestart($class='normal') {
	if ($this->offline) {
		if ($class=='ok') echo "** ";
		if ($class=='warning') echo "?? ";
		if ($class=='error') echo "!! ";	
	} else {
		echo "<p class=\"$class\">";
	}
}
function lineend() {
	if (!$this->offline) echo "</p>";
	echo "\n";
}

function debug($message) {
	$this->linestart('debug');
	$this->output($message);
	$this->lineend();
}

function info($message) {
	// TODO convert arrays to PRE blocks in online mode
	$this->linestart();
	$this->output($message);
	$this->lineend();
}

function warn($message) {
	$this->linestart('warning');
	$this->output($message);
	$this->lineend();
}

function error($message) {
	$this->hasErrors = true;
	$this->linestart('error');
	$this->output($message);
	$this->lineend();
}

/**
 * Fatal error causes output to end and script to exit.
 * It is assumed that fatal errors are handled manually by the administrator.
 */
function fatal($message, $code = 1) {
	$this->error( $message );
	// TODO this method shouldn't be herer, right?
}

// internal, no HTML here because it is used both online and offline
function output($message) {
	if (is_array($message))
		$message = $this->formatArray($message);
	echo $message;
}

function formatArray($message) {
	$msg = '';
	$linebreak = "\n";
	if (!$this->offline) $linebreak = "<br />".$linebreak;
	foreach ( $message as $key=>$val ) {
		if ( $val===false )
			$val = 0;
		if ( is_string($key) )
			$msg .= "$key: ";
		$msg .= "$val$linebreak";
	}
	// remove last linebreak
	$last = strlen($msg)-strlen($linebreak);
	if ( $last>=0 )
		$msg = substr( $msg, 0, $last);
	return $msg;
}

function _pageEnd($code = 0) {
	if (!$this->offline) echo "</body></html>\n\n";
	exit( $code );
}

} // end report class

// formatted date
function getTime() {
    return '<span class="datetime">'.date("Y-m-d\TH:i:sO").'</span>';
}

// temporary solution, TODO make admin a class that takes a report instance as constructor argument
$report = new Report();
function debug($message) {global $report; $report->debug($message); }
function info($message) {global $report; $report->ok($message); }
function warn($message) {global $report; $report->warn($message); }
function error($message) {global $report; $report->error($message); }
function fatal($message) {global $report; $report->fatal($message); }
function html_end($code = 0) {global $report; $report->display(); }

// --- basic repository examination ---
// read-only operations. The actual processing is in the backup script which uses these functions.

/**
 * @return true if path points to a repository accessible using svnlook
 */
function isRepository($localPath) {
	if (!file_exists($localPath))
		return false;
	$command = getCommand("svnlook") . " uuid $localPath";
	$output = array();
	$return = 0;
	$uuid = exec($command, $output, $return);
	if ($return!=0)
		return false;
	return strlen($uuid) > 0;	
}

/**
 * The same HEAD revision number must be used thorughout backup, or a concurrent transaction could cause invalid backup
 * @return revision number integer
 */
function getHeadRevisionNumber($repository) {
	$command = getCommand("svnlook") . " youngest $repository";
	$output = array();
	$return = 0;
	$rev = (int) exec($command, $output, $return);
	if ($return!=0)
		fatal ("Could not get revision number using $command");
	return $rev;
}

/**
 * @return listing of the current backup files named fileprefix* in backupPath
 */
function getCurrentBackup($backupPath, $fileprefix) {
	// check arguments
	if ( ! file_exists($backupPath) )
		fatal("backupPath '$backupPath' does not exist");
	// get backup files in directory
	$files = getDirContents($backupPath,$fileprefix);
	if ( count($files)==0 )
		warn("Directory '$backupPath' contains no files named $fileprefix*. This must be the first backup.");
	return getBackupInfo($files, $fileprefix);
}

// --- helper functions ---

/**
 * Send message to the administrator whose address is specified in repos.properties
 */
function notifyAdministrator($text) {
	$address = getConfig('administrator_email');
	error("Did not notifyAdministrator. Method not implemented.");
}

// --- backup support

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
 * @return true if string starts with the given prefix (case-insensitive). Empty prefix returns true.
 */
function startsWith($string, $prefix) {
	if ( strlen($prefix)==0 )
		return true;
	return strncasecmp ( $string, $prefix, strlen($prefix) ) == 0;
}

/**
 * Get files and subdirectories in directory.
 * @param directory Path to check
 * @param startsWith Optional. Include only names that start with this prefix. 
 * @return Filenames as array sorted alpabetically
 */
function getDirContents($directory, $startsWith="") {
	if ( ! file_exists($directory) )
		warn( "Directory $directory does not exist" );
	$filelist = array();
	if ($dir = opendir($directory)) {
	   while (false !== ($file = readdir($dir))) { 
		   if ( $file != ".." && $file != "." && startsWith($file,$startsWith) ) {
				$filelist[] = $file;
		   }
	   }
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
	$c = count($files);
	if ( $c==0 )
		return array();
	$mapargs = array_fill( 0, $c, $startsWith );
	return array_map( 'getRevisionInfo', $files, $mapargs);
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

// ----- unit tests ----
if ( isTestRun() ) {
	html_start("Unit testing " . basename(__FILE__));
	
	debug("---- testing: upOne ----");
	assertEquals("/some/path",upOne("/some/path/child"));
	
	debug("---- testing: startsWith ----");
	assertEquals(true, startsWith("hepp","") );
	assertEquals(true, startsWith("hepp","he") );
	assertEquals(true, startsWith("hepp","hepp") );
	assertEquals(false, startsWith("","hepp") );
	assertEquals(false, startsWith("hepp","hopp") );
	assertEquals(false, startsWith("hep","hepp") );
				
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
	
	debug("---- testing: getBackupInfo ----");
	$files = array(
		getFilename( getPrefix("/path/to/repo"), 3, 12),
		getFilename( getPrefix("/path/to/repo"), 100, 1000),
		getFilename( getPrefix("/path/to/repo"), 1111111, 1111111)
		);
	$revs = getbackupInfo( $files, getPrefix("/path/to/repo") );
	assertEquals( getFilename( getPrefix("/path/to/repo"), 1111111, 1111111), $revs[2][0], "name of third file");
	assertEquals( 3, $revs[0][1] );
	assertEquals( 12, $revs[0][2] );
	assertEquals( 100, $revs[1][1] );
	assertEquals( 1000, $revs[1][2] );
	assertEquals( 1111111, $revs[2][1] );
	assertEquals( 1111111, $revs[2][2] );
		
	debug("---- testing: getDirContents ----");
	$dir = getConfig("backup_folder");
	$prefix = getPrefix( getConfig("local_path") );
	debug("This test depends on file in backup_folder $dir, and local_path wich gives prefix $prefix");
	$files1 = getDirContents($dir);
	$total = count($files1);
	if ( $total<1 )
		error("No files found in $dir");
	$files2 = getDirContents($dir, $prefix);
	$filtered = count($files2);
	if ( $filtered<1 )
		warn("No files found in $dir with prefix $prefix");
	if ( $total < $filtered )
		error( "Filtering with prefix makes $total files become $filtered, which is very strange");
	if ( $total == $filtered )
		warn( "Total number of files is same as filtered, this may indicate that $prefix filtering makes no difference");
		
	// debug("---- testing:  ----");
	
	global $hasErrors;
	if( $hasErrors )
		fatal("There were test errors");
	else
		info("All tests passed");
		
	html_end();
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
