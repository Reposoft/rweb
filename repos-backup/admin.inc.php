<?php
/**
 * This file contains helper functions for backup administration (such as file name resolution).
 * It also contains a small library to make HTML output from a batch job.
 * 
 * @package admin
 */

require( dirname(__FILE__).'/reposweb.inc.php' );
require( ReposWeb.'conf/System.class.php' );
require( ReposWeb.'conf/repos.properties.php' );
require( ReposWeb.'conf/Command.class.php' );

define('TEMP_FILE_EXTENSION', '.temporary');

// --- command line parameters ---
if (isset($argc) && $argc>1) {
	function repos_config_arg($a) {
		if (preg_match('/^--([a-z-]+)=(.*)$/', $a, $m)) {
			$conf = 'REPOS_'.strtoupper(strtr($m[1],'-','_'));
			$_SERVER[$conf] = $m[2];
		} else {
			trigger_error('Unrecognized repos argument '.$a, E_USER_ERROR);
		}
	}
	for ($i=1; $i<count($argv); $i++) repos_config_arg($argv[$i]);
}

// --- configuration ---

/**
 * Default config for repos-backup is derived from a common parent folder.
 * Anyone who doesn't like this default can set explicit config values like Folder and Repo.
 * @return default folder containing backup structure
 */
function getBackupHostDefaultRoot() {
	return getParent(getDocrootDefault());
}

/**
 * @return the configured folder for backup data, with fallback to a repos default
 */
function getBackupFolder() {
	$b = isset($_SERVER['REPOS_BACKUP_FOLDER'])
		? $_SERVER['REPOS_BACKUP_FOLDER']
		: getBackupHostDefaultRoot().'backup/';
	//validate
	if ( !preg_match("/[\\/\\\\]$/", $b) ) trigger_error('Backup folder path must end with slash', E_USER_ERROR);
	return $b;
}

/**
 * @return path to the local subversion repository folder for svnadmin access, with trailing slash
 */
function getBackupRepo() {
	// not depending on repos-admin //return getAdminLocalRepo();
	$r = isset($_SERVER['REPOS_LOCAL_REPO'])
		? $_SERVER['REPOS_LOCAL_REPO']
		: getBackupHostDefaultRoot().'repo/';
	return $r;
}

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
function info($message) {global $report; $report->ok(getTime().' '.$message); }
function fail($message) {global $report; $report->fail(getTime().' '.$message); }
function warn($message) {global $report; if($report) $report->warn(getTime().' '.$message); }
function error($message) {global $report; $report->error(getTime().' '.$message); }
function fatal($message) {global $report; $report->fatal(getTime().' '.$message); } // deprecated
function html_end($code = 0) {global $report; $report->display(); }

// --- basic repository examination ---
// read-only operations. The actual processing is in the backup script which uses these functions.

/**
 * @return true if path points to a repository accessible using svnlook
 */
function isRepository($localPath) {
	if (!file_exists($localPath))
		return false;
	// performance is not an issue here so we have time to validate the command first
	$command = new Command('svnlook');
	$command->addArg('--version');
	if ($command->exec()) trigger_error('The svnlook executable is not properly configured', E_USER_ERROR);
	// check repository
	$command = new Command('svnlook');
	$command->addArgOption('uuid', $localPath);
	if ($command->exec()) return false;
	return strlen(array_pop($command->getOutput())) > 0;	
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
		trigger_error("Could not get revision number using $command", E_USER_ERROR);
	return $rev;
}

/**
 * @return listing of the current backup files named fileprefix* in backupPath
 */
function getCurrentBackup($backupPath, $fileprefix) {
	// check arguments
	if ( ! file_exists($backupPath) )
		trigger_error("backupPath '$backupPath' does not exist", E_USER_ERROR);
	// get backup files in directory
	$files = getDirContents($backupPath,$fileprefix);
	if ( count($files)==0 )
		warn("Directory '$backupPath' contains no files named $fileprefix*. This must be the first backup.");
	return getBackupInfo($files, $fileprefix);
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
	if ($number > 2147483647)
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
