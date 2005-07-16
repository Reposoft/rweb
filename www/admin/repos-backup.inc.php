<?php

/**
 * Functions for reading and writing an ordered list of incremental SVN dumpfiles
 */

require( dirname(__FILE__) . '/repos-admin.inc.php' );

define('TEMP_DIR',"/tmp");
define('BACKUP_SCRIPT_VERSION',"\$LastChangedRevision$");

/**
 * Create a repository in the given local path
 * @param repository absolute path. If the directory does not exist it is created.
 * @return true if successful
 */
function create($repository) {
	if ( ! file_exists($repository) ) {
		debug ("Creating repository directory $repository");
		mkdir($repository, 0700);
	}
	$command = getCommand("svnadmin") . " create $repository";
	$output = array();
	$return = 0;
	$result = (int) exec($command, $output, $return);
	info("$command said $result and outputted: " . $output);
	return ( $return==0 );
}

/**
 * Produce a compressed backup of new revisions in a repository
 * @param backupPath Path containing the previous backups of the repository, if directory is empty the entire repository will be backed up. No tailing slash.
 * @param repository Local path of the repository to back up
 * @param fileprefix
 */
function dump($repository, $backupPath, $fileprefix) {
	$current = getCurrentBackup( $backupPath, $fileprefix );
	$files = count($current);
	// The same HEAD revision number must be used thorughout backup, or a concurrent transaction could cause invalid backup
	$headrev = getHeadRevisionNumber( $repository );
	$fromrev = 0;
	if ( $files>0 )
		$fromrev = $current[$files-1][2] + 1;
	if ( $fromrev - 1 > $headrev )
		fatal(getTime()." Serious error in $repository backup. Backup has more revisions ($fromrev) than repository ($headrev)." );
	if ( $fromrev - 1 == $headrev ) {
		debug(getTime()." No further backup needed for $repository. Both dumpfiles and repository are at revision $headrev." );
		return;
	}
	$success = dumpIncrement($backupPath, $repository, $fileprefix, $fromrev, $headrev);
	if ( ! $success )
		fatal(getTime()." Could not dump $repository revision $fromrev to $headrev to folder $backupPath");
	info(getTime()." Dumped $repository revision $fromrev to $headrev to folder $backupPath");
}

/**
 * @param backupPath no tailing slash
 * @return true if successful, in which case there is a $backupPath/$fileprefix[revisions].svndump.gz file
 */
function dumpIncrement($backupPath, $repository, $fileprefix, $fromrev, $torev) {
	$extension = ".svndump";
	$filename = getFilename( $fileprefix, $fromrev, $torev ) . $extension;
	$path = $backupPath . DIRECTORY_SEPARATOR . $filename;
	$command = getCommand("svnadmin") . " dump $repository --revision $fromrev:$torev --incremental --deltas";
	$tmpfile = tempnam(TEMP_DIR, "svn");
	if ( isWindows() )
		$command .=  " > $tmpfile";
	else
		$command .= " | gzip -9 > $path.gz";
	$output = array();
	$return = 0;
	exec($command, $output, $return);
	if ( $return != 0 )
		return false;
	// in windows file has not been compressed in the first command
	if ( isWindows() ) {
		$success = gzipInternal($tmpfile,"$path.gz");
		unlink($tmpfile);
		if ( ! $success ) return false;
	}
	createMD5("$path.gz");
	return true;
}

/**
 * Load backup into existing repository using 'svnadmin load'.
 * Starts loading from the current revision number of the repository,
 * assuming matching revision numbers of the backup files.
 * @param backupPath Absolute path to directory that contains the dump files. No tailing slash.
 * @param repository Absolute path of the repository to load to
 * @param fileprefix Filenames up to first revision number, for example "myrepo-" for myrepo-00?-to-0??.svndump.gz
 */
function load($repository, $backupPath, $fileprefix) {
	define("LOADCOMMAND",getCommand('svnadmin') . " load $repository");

	// validate input
	if ( strlen($backupPath)<3 )
		fatal("backupPath not set");
	if ( strlen($repository)<3 )
		fatal("repository not set");	

	// check preconditions derived from input
	debug("Reading backup files starting with '$fileprefix' in $backupPath");
	if ( !isRepository($repository) )
		fatal("repository '$repository' is not accessible");
	
	$backup = getCurrentBackup($backupPath, $fileprefix);
	// start from current revision in repository
	$startrev = getHeadRevisionNumber($repository);
	foreach ($backup as $file) {
		if ( $file[2] < $startrev ) {
			debug("Revision $file[1] to $file[2] already in repository, skipping $file[0]");
			continue;
		}
		if ( $file[1] != $startrev )
			fatal("Revision number gap at $file[0] starting at revision $file[1], repository is at revision " . ($startrev - 1));
		// read the files into repo
		$startrev = $file[2] + 1;
		loadDumpfile($backupPath . DIRECTORY_SEPARATOR . $file[0],LOADCOMMAND);
	}
	$head = getHeadRevisionNumber($repository);
	if ($head < $lastrev)
		fatal("Not all backup revisions have been loaded. Repository is at rev $head while backup goes up to $lastrev");
	info( "Successfuly loaded backup revisions up to $lastrev. Repository $repository is now at revision $head." );
}

/**
 * Verify a repository
 * @param repository absolute path to repository
 * @return true if repository is valid
 */
function verify($repository) {
	define("VERIFYCOMMAND", getCommand('svnadmin') . " verify $repository" );
	$return = 0;
	exec( VERIFYCOMMAND, $output, $return );
	if ( $return == 0 )
		info( "Repository $repository verified and seems OK." );
	else
		error( VERIFFYCOMMAND . " returned code $return. Repository is not valid." );
	return $return==0;
}

/**
 * Verify md5-sum of file(s) against MD5SUMS file in the same folder.
 * @param path Absolute path of the file to verify. If it is a directory, verify all files included in the MD5SUMS file.
 * @return true if all files valid
 */
function verifyMD5($path) {
	$sums = getMD5sums( $path );
	$ok = true;
	foreach ( $sums as $file => $md5 ) {
		if ( ! file_exists( $path . DIRECTORY_SEPARATOR . $file ) ) {
			error( "File $file listed in MD5SUMS file does not exist in $path" );
			continue;
		}
		$sum = md5_file( $path . DIRECTORY_SEPARATOR . $file );
		if ($sum != $md5) {
			error( "Incorrect MD5 sum for $file, calculated to $sum but is supposed to be $md5" );
			$ok = false;
		} else {
			debug( "MD5 sum for $file is OK" );
		}
	}
	if ($ok)
		debug("All md5 sums match in dir $path");
	return($ok);
}

/**
 * @param path absolute path to file
 * @return false if sums don't match
 */
function verifyFileMD5($path) {
	if ( ! file_exists( $path ) )
		fatal( "File $path does not exist so it can't be verified" );
	$sums = getMD5sums(dirname($path));
	$sum = md5_file( $path );
	//debug("MD5 for $path (stored): $sum, (".$sums[basename($path)].")");
	return $sums[basename($path)] == $sum;
}

/**
 * Get stored MD5 sums for directory as array
 * @param dir PAth with no tailing slash.
 * @return alla MD5 sums in file as array filename=>md5sum
 */
function getMD5sums($dir) {
	$sumsfile = $dir . DIRECTORY_SEPARATOR . "MD5SUMS";
	if ( ! file_exists( $sumsfile ) )
		error( "There is no MD5SUMS file in directory $dir" );
	$sums = file( $sumsfile );
	$ret = array();
	foreach ( $sums as $line ) {
		list($md5, $filename) = explode("  ",trim($line)); // note that separator is two spaces
		$ret[$filename] = $md5;
	}
	return $ret;
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
 * @param file the compressed dumpfile to load
 * @param loadcommand the svnadmin load command, excluding input pipe
 */
function loadDumpfile($file,$loadcommand) {
	if ( ! verifyFileMD5($file) )
		error( "File $file has incorrect MD5 sum. Trying anyway." );
	$command = '';
	$tmpfile = tempnam(TEMP_DIR, "svn");
	if ( isWindows() ) {
		if ( ! gunzipInternal($file,$tmpfile) ) fatal("Could not extract file $file");
		$command = "$loadcommand < $tmpfile";
	} else {
		$command = getCommand('gunzip') . " -c $file | $loadcommand";
	}
	$return = 0;
	debug("Executing: $command");
	exec( $command, $output, $return);
	//unlink( $tmpfile );
	return $return;
}

/**
 * Gunzip file using php functions
 * Does not remove original file
 * @return true if successful, meaning there is now another file. false on any error
 */
function gunzipInternal($compressedfile, $tofile) {
	$fp = fopen($tofile, "w") ;
	if ( ! $fp ) return false;
	$zp = gzopen($compressedfile, "r");
	$sum = 0;
	if ($zp) {
	  	while (!gzeof($zp)) {
			$buff1 = gzgets ($zp, 4096) ;
			fputs($fp, $buff1) ;
			$sum += strlen($buff1);
		} 
		gzclose($zp);
		fclose($fp);
		debug("Wrote $sum bytes to $tofile, from compressed $compressedfile.");
	} else {
		fclose($fp);
		return false;
	}
	return true;   
}

/**
 * Compress file with gzip.
 * @param originalfile Full path of uncompressed file.
 * @param tofile Full path of target file, must be different than original.
 * @return true if successful, meaning there is now another file. false on any error
 */
function gzipInternal($originalfile, $tofile) {
	$fp = fopen($originalfile, "r") ;
	if ( ! $fp ) return false;
	$zp = gzopen($tofile, "w");
	$sum = 0;
	if ($zp) {
		while (!feof($fp)) {
			$buff1 = fgets ($fp, 4096) ;
			gzputs($zp, $buff1) ;
			$sum += strlen($buff1);
		} 
		gzclose($zp);
		fclose($fp);
		debug("Read $sum bytes from $originalfile, wrote to compressed $tofile.");
	} else {
		fclose($fp);
		return false;
	}
	return true;
}
?>
