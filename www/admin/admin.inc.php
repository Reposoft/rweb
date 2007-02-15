<?php
/**
 * This file contains helper functions for backup administration (such as file name resolution).
 * It also contains a small library to make HTML output from a batch job.
 * 
 * @package admin
 */
require_once( dirname(dirname(__FILE__)) . "/conf/System.class.php" );
require_once( dirname(dirname(__FILE__)) . "/conf/repos.properties.php" );

define('TEMP_FILE_EXTENSION', '.temporary');

// --- output functions ---

// override theme function
function getTheme() {
	return "/themes/simple"; 
}

function getTime() { // still used in backup
	return getReportTime();
}

// Currently all shared backup methods require a global Report instance $report
function html_start() {}
function debug($message) {global $report; $report->debug($message); }
function info($message) {global $report; $report->ok($message); }
function fail($message) {global $report; $report->fail($message); }
function warn($message) {global $report; $report->warn($message); }
function error($message) {global $report; $report->error($message); }
function fatal($message) {global $report; $report->fatal($message); } // deprecated
function html_end($code = 0) {global $report; $report->display(); }

// --- basic repository examination ---
// read-only operations. The actual processing is in the backup script which uses these functions.

/**
 * @return true if path points to a repository accessible using svnlook
 */
function isRepository($localPath) {
	if (!file_exists($localPath))
		return false;
	$command = System::getCommand("svnlook") . " uuid $localPath";
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
	$command = System::getCommand("svnlook") . " youngest $repository";
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
 * Creates a filename prefix for bakup files
 * @return valid filename representing an absolute repository path, ending with "-"
 */
function getPrefix($repository) {
	if ( ':'==substr($repository,1,1) )
		$repository = substr($repository,2);
	// return ( "svnrepo" . rtrim(strtr($repository, "/\\", "--"),"-") . "-" );
	return 'repos-'.getRepositoryName($repository).'-';
}

/**
 * Guesses a repository name based on the location in the file system.
 *
 * @param String $repository the local repository path, absolute
 * @return String suggested name of repository, for use in backup file names
 */
function getRepositoryName($repository) {
	if ( ':'==substr($repository,1,1) )
		$repository = substr($repository,2);
	$nonsignificant = array('repo');
	$parts = explode('/', strtr($repository, "\\", "/"));
	for ($i = count($parts)-1; $i>=0; $i--) {
		if (strlen($parts[$i]) < 1) continue;
		if (in_array(strtolower($parts[$i]), $nonsignificant)) continue;
		return _cleanUpName($parts[$i]);
	}
	// unlikely but possible, best guess is full path
	return _cleanUpName(trim(strtr($repository, "/\\", "--"),"-"));
}

function _cleanUpName($filename) {
	return strtolower(strtr($filename, ' ', '_'));
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
function getDirContents($directory, $startsWith="") {
	if ( ! file_exists($directory) )
		warn( "Directory $directory does not exist" );
	$filelist = array();
	if ($dir = opendir($directory)) {
	   while (false !== ($file = readdir($dir))) { 
	   	if ( strEnds($file, TEMP_FILE_EXTENSION)) {
	   		warn("Backup folder contains unfinished file '$file'");
	   	} elseif ( $file != ".." && $file != "." && strBegins($file,$startsWith) ) {
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

?>
