<?php

require( dirname(__FILE__) . '/repos-admin.inc.php' );

// test
// load( getLocalPath('/srv/backup'), getLocalPath('~/testrepo'), 'svnrepo-testrepo-' );
// verify( getLocalPath('~/test') ); 
dump( getLocalPath('~/testbackup'), getLocalPath('~/test'), getPrefix("/home/solsson/test") );

// Functions for reading and writing an ordered list of incremental SVN dumpfiles

function dump($backupPath, $repository, $fileprefix) {
	$current = getCurrentBackup( $backupPath, $fileprefix );
	$files = count($current);
	$headrev = getHeadRevisionNumber( $repository );
	$fromrev = 0;
	if ( $files>0 )
		$fromrev = $current[$files-1][2] + 1;
	$success = dumpIncrement($backupPath, $repository, $fileprefix, $fromrev, $headrev);
	if ( ! $success )
		fatal("Could not dump $repository revision $fromrev to $headrev to folder $backupPath");
	debug("Dumped $repository revision $fromrev to $headrev to folder $backupPath");
}

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
 * @param backupPath no tailing slash
 * @return true if successful
 */
function dumpIncrement($backupPath, $repository, $fileprefix, $fromrev, $torev) {
	$extension = ".svndump";
	$filename = getFilename( $fileprefix, $fromrev, $torev ) . $extension;
	$path = $backupPath . DIRECTORY_SEPARATOR . $filename;
	$command = getCommand("svnadmin") . " dump $repository --revision $fromrev:$torev --incremental --deltas";
	if ( isWindows() )
		$command .=  " > $path";
	else
		$command .= " | gzip -9 > $path.gz";
	$output = array();
	$return = 0;
	exec($command, $output, $return);
	if ( $return != 0 )
		return false;
	if ( isWindows() )
		echo "TODO: gzip $path";
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
		fatal("Directory '$backupPath' contains no files named $fileprefix*");
	return getBackupInfo($files, $fileprefix);
}

/**
 * Load backup into repository using 'svnadmin load'
 * @param backupPath Absolute path to directory that contains the dump files. No tailing slash.
 * @param repository Absolute path of the repository to load to
 * @param fileprefix Filenames up to first revision number, for example "myrepo-" for myrepo-00?-to-0??.svndump.gz
 */
function load($backupPath, $repository, $fileprefix) {
	define("LOADCOMMAND",getCommand('svnadmin') . " load $repository");

	// validate input
	if ( strlen($backupPath)<3 )
		fatal("backupPath not set");
	if ( strlen($repository)<3 )
		fatal("repository not set");	

	// check preconditions derived from input
	debug("Reading backup files starting with '$fileprefix' in $backupPath");
	if ( ! file_exists($repository) )
		fatal("repository '$repository' does not exist");
	
	$backup = getCurrentBackup($backupPath, $fileprefix);
	$lastrev = -1;
	foreach ($backup as $file) {
		if ( ! $file[1] == $lastrev + 1 )
			fatal("Revision number gap at $file[0] starting at revision $file[1], last revision was $lastrev");
		// read the files into repo
		$lastrev = $file[2];
		loadDumpfile($backupPath . '/' . $file[0],LOADCOMMAND);
	}
	
}

/**
 * Verify a repository
 * @param repository absolute path to repository
 * @return true if repository is valid
 */
function verify($repository) {
	define("VERIFYCOMMAND", getCommand('svnadmin') . " verify $repository" );
	$output = array();
	$return = 0;
	exec( VERIFYCOMMAND, $output, $return );
	if ( $return == 0 )
		debug( "$repository verified with return code $return, output: \n" . implode(" \n",$output) );
	else
		error( VERIFFYCOMMAND . " returned code $return, output: $output" );
	return $return==0;
}

function verifyMD5($file) {
	$sumsfile = dirname($file) . "/MD5SUMS";
	// ...
}

/**
 * Create MD5-sum for backup file $file and appends to MD5SUMS file
 * @param file Absolute path to file. MD5SUMS will be looked for in the same directory.
 * @return the md5 sum
 */
function createMD5($file) {
	$sumsfile = dirname($file) . "/MD5SUMS";
	if ( ! file_exists($file) )
		fatal("File '$file' does not exist. Cannot do md5sum.");
	$hash = md5_file( $file );
	if ($fp = fopen($sumsfile, 'a')) {
         fwrite($fp, $hash . "  " . basename($file) . "\n");
		 fclose($fp);
   	} else {
		fatal("Could not append to sums file $sumsFile");
	}
}

/**
 * @return return value of the resulting command
 */
function loadDumpfile($file,$loadcommand) {
	global $isWindows;
	$command = '';
	$tmpfile = tempnam("/tmp", "svn");
	if ($isWindows) {
		gunzipInternal($file,$tmpfile);
		$command = "$loadcommand < $tmpfile";
	} else {
		$command = getCommand('gunzip') . " -c $file | $loadcommand";
	}
	debug("Executing: $command");
	$output = array();
	$return_val = 0;
	exec( $command, $output, $return_val);
	debug( $output );
	unlink($tmpfile);
	return $return_val;
}

/**
 * Gunzip file using php functions
 */
function gunzipInternal($fromfile, $tofile) {
	$fp = fopen("$tofile", "w") ;
	$zp = gzopen("$fromfile", "r");
	if ($zp) {
	  while (!gzeof($zp))
	  {
	   $buff1 = gzgets ($zp, 4096) ;
	   fputs($fp, $buff1) ;
	  }               
	} else {
		fatal("Could not extract file $fromfile");
	}   
	gzclose($zp) ;
	fclose($fp) ;
}
?>
