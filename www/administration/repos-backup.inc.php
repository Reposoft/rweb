<?php

require( dirname(__FILE__) . '/repos-admin.inc.php' );

// test
start( 'test load' );
load( getLocalPath('/srv/backup'), getLocalPath('/srv/repos/staffan'), 'svnrepo-srv-repos-staffan-' );
	
// Functions for reading and writing an ordered list of incremental SVN dumpfiles

function dump() {

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
	if ( ! file_exists($backupPath) )
		fatal("backupPath '$backupPath' does not exist");
	debug("Reading backup files starting with '$fileprefix' in $backupPath");
	
	if ( ! file_exists($repository) )
		fatal("repository '$repository' does not exist");
	
	// iterate over the list of files
	$allfiles = getFilesInDir($backupPath);
	if ( count($allfiles)==0 )
		fatal("Directory '$backupPath' is empty");
		
	// initiate looping over filenames
	$revto = -1;
	foreach ($allfiles as $filename) {
		// check that this is a backup file
		if ( stristr($filename,$fileprefix)!=$filename )
			continue;
		// extract revision numbers from filename
		$rev = array();
		if ( 0==ereg( '[0]*([0-9][0-9]*)-to-[0]*([0-9][0-9]*).*', substr($filename,strlen($fileprefix)), $rev) )
			fatal("Could not extract revision numbers from filename ($fileprefix)$rev[0]");
		if ( ! $rev[1] == $revto + 1 )
			fatal("Revision number gap at $filename starting at revision $rev[1], last revision was $revto");
		// read the files into repo
		$revto = $rev[2];
		loadDumpfile($backupPath . '/' . $filename,LOADCOMMAND);
	}
	
}

/**
 * Verify a repository
 * @param repository absolute path to repository
 * @return true if repository is valid
 */
function verify($repository) {
	define("VERIFYCOMMAND", getSvnCommand('svnadmin') . " verify $repository" );
	$output = array();
	$return = 0;
	exec( VERIFYCOMMAND, $output, $return );
	debug( $output );
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

/**
 * Get backup files base name from repository path
 */
function getFilename($repository) {

}
?>
