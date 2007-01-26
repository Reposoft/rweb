<?php
/**
 * Functions for reading and writing an ordered list of incremental SVN dumpfiles
 *
 * @package admin
 */

require( dirname(__FILE__) . '/admin.inc.php' );
require( dirname(dirname(__FILE__)) . '/conf/Command.class.php' );

define('TEMP_DIR',getTempDir('backup'));
define('BACKUP_SCRIPT_VERSION','$LastChangedRevision$');
define('BACKUP_SIZE', 100*1024*1024); // recommended unpacked size of dump files
define('BACKUP_MAX_TIME', 30*60); // maximum time in seconds for dumping and packing one backup increment (with the above size)

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
	debug("$command said $result and outputted: " . $output);
	// TODO ok message
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
		info(getTime()." No further backup needed for $repository. Both dumpfiles and repository are at revision $headrev." );
		return;
	}
	$success = dumpIncrement($backupPath, $repository, $fileprefix, $fromrev, $headrev);
	if ( ! $success )
		fatal(getTime()." Could not dump $repository revision $fromrev to $headrev to folder $backupPath");
	info(getTime()." Dumped $repository revision $fromrev to $headrev to folder $backupPath");
}

/**
 * Creates backup archive(s) for a revision interval in the repository.
 * When the dumpfile size exceeds BACKUP_SIZE, the current contents will be
 * compressed and a new file is started.
 * @param String $backupPath the folder that contains backup file, with trailing slash
 * @param String $repository the local repository path, with trailing slash
 * @param String $fileprefix first part of the filename of dumpfiles
 * @return true if successful, in which case there is a $backupPath/$fileprefix[revisions].svndump.gz file
 */
function dumpIncrement($backupPath, $repository, $fileprefix, $fromrev, $torev) {
	set_time_limit(BACKUP_MAX_TIME); // each increment can take 30 minutes to back up
	$starttime = time();
	$extension = ".svndump";
	// get a new empty file
	$tmpfile = tempnam(rtrim(TEMP_DIR,'/'), "svn");
	touch($tmpfile);
	// dump every revision separately and check size after each operation
	for ($i = $fromrev; $i<=$torev; $i++) {
		$command = new Command('svnadmin');
		$command->addArgOption('dump');
		$command->addArgOption('--revision '.$i);
		$command->addArgOption('--incremental');
		//not wanted, see svnbook//$command->addArgOption('--deltas');
		$command->addArg($repository);
		$command->setOutputToFile($tmpfile, true);
		$command->exec();
		if ($command->getExitcode()) fatal("Could not dump repository contents.");
		clearstatcache(); // so that size is not cached
		$size = filesize($tmpfile);
		if ($size > BACKUP_SIZE && $i < $torev) {
			// split into several dumpfiles
			$filename = getFilename( $fileprefix, $fromrev, $torev ) . $extension;
			info('Saved '.$size.' bytes in '.(time()-$starttime).' seconds');
			if ((time()-$starttime) > (BACKUP_MAX_TIME / 3)) fatal("Will not have time to compress and verify within ".BACKUP_MAX_TIME." seconds.");
			return packageDumpfile($tmpfile, $backupPath.getFilename($fileprefix, $fromrev, $i).$extension) 
				&& dumpIncrement($backupPath, $repository, $fileprefix, $i+1, $torev);
		}
	}
	info('Saved up to current revision, '.$size.' bytes, in '.(time()-$starttime).' seconds');
	if ((time()-$starttime) > (BACKUP_MAX_TIME / 3)) fatal("Will not have time to compress and verify within ".BACKUP_MAX_TIME." seconds.");
	return packageDumpfile($tmpfile, $backupPath.getFilename($fileprefix, $fromrev, $torev).$extension);
}

/**
 * Compress dumpfile and validate that the compressed contents are same as the original file.
 *
 * @param unknown_type $tmpfile
 * @param unknown_type $path
 * @return unknown
 */
function packageDumpfile($tempfile, $path) {
	$starttime = time();
	clearstatcache();
	$size = filesize($tempfile);
	$originalmd5 = _calculateMD5($tempfile);
	// the only thing we really need to do, rest is verification
	$pack = gzipInternal($tempfile,"$path.gz.incomplete");
	if (!$pack) fatal("Backup file $tempfile is empty or could not be compressed to $path.gz.");
	if ($size != $pack) warn("Dumpfile is $size bytes but wrote $pack to compressed target.");
	rename("$path.gz.incomplete", "$path.gz");
	createMD5("$path.gz");
	// uncompress to validate
	$back = gunzipInternal("$path.gz", $tempfile);
	if (!$back) error("Could not unpack the compressed file $path.gz");
	if ($size != $back) warn("Dumpfile was $size bytes but uncompressed contents are $back.");
	$samemd5 = _calculateMD5($tempfile);
	if ($originalmd5 != $samemd5) error("MD5 sum for original contents does not match unpacked.");
	info("Compressed ".basename($path)." with original MD5 sum ".$originalmd5.' in '.(time()-$starttime).' seconds');
	// done
	deleteFile(toPath($tempfile));
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
	$head = getHeadRevisionNumber($repository);
	foreach ($backup as $file) {
		if ( $head > 0 && $file[2] <= $head ) {
			debug("Revision $file[1] to $file[2] already in repository, skipping $file[0]");
			continue;
		}
		if ( $head > 0 && $file[1] != $head + 1 )
			fatal("Revision number gap at $file[0] starting at revision $file[1], repository is at revision " . $head);
		// read the files into repo
		set_time_limit(BACKUP_MAX_TIME);
		$head = $file[2];
		$return = loadDumpfile($backupPath . $file[0],LOADCOMMAND);
		if ($return != 0) {
			fatal("Error loading backup file $file[0], returned $return. Repository loading stopped.");
		}
	}
	info( "Successfuly loaded backup revisions up to " . $head . " into repository $repository." );
}

/**
 * Verify a repository
 * @param repository absolute path to repository
 * @return true if repository is valid
 */
function verify($repository) {
	define("VERIFYCOMMAND", getCommand('svnadmin') . " verify $repository" );
	set_time_limit(BACKUP_MAX_TIME);
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
		set_time_limit(BACKUP_MAX_TIME);
		if ( ! file_exists( $path . DIRECTORY_SEPARATOR . $file ) ) {
			error( "File $file listed in MD5 sums file does not exist in $path" );
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
		info("All md5 sums match in dir $path");
	return($ok);
}

/**
 * @param path absolute path to file
 * @return false if sums don't match
 */
function verifyFileMD5($path) {
	if ( ! file_exists( $path ) ) {
		fatal( "File $path does not exist so it can't be verified" );
	}
	$sums = getMD5sums(dirname($path));
	$sum = md5_file( $path );
	//debug("MD5 for $path (stored): $sum, (".$sums[basename($path)].")");
	$filename = basename($path);
	if (!isset($sums["$filename"])) {
		fatal("There is no MD5 sum for file '$filename'. Backup is probably incomplete. "
		."The file '$filename' and later files on the primary server should be deleted, "
		."so the next backup job can create a complete backup.");
	}
	return $sums["$filename"] == $sum;
}

/**
 * @param String backup folder with trailing slash
 * @return String the full path to the default MD5-sums file
 */
function _getMD5File($folder) {
	// windows applications like md5summer expect a file extension
	return $folder . "repos-backup.md5";
}

/**
 * Get stored MD5 sums for directory as array
 * @param dir Path with no trailing slash.
 * @return alla MD5 sums in file as array filename=>md5sum
 */
function getMD5sums($dir) {
	$sumsfile = _getMD5File($dir . DIRECTORY_SEPARATOR);
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
	$sumsfile = _getMD5File(dirname($file) . "/");
	if ( ! file_exists($file) )
		fatal("File '$file' does not exist. Cannot do md5sum.");
	$hash = _calculateMD5( $file );
	if ($fp = fopen($sumsfile, 'a')) {
         fwrite($fp, $hash . "  " . basename($file) . "\n");
		 fclose($fp);
   	} else {
		fatal("Could not append to sums file $sumsFile");
	}
}

function _calculateMD5($file) {
	return md5_file( $file );
}

/**
 * @return 1 if backup file is invalid, otherwise return value of the resulting command
 * @param file the compressed dumpfile to load
 * @param loadcommand the svnadmin load command, excluding input pipe
 */
function loadDumpfile($file,$loadcommand) {
	if ( ! verifyFileMD5($file) ) {
		error( "File $file has incorrect MD5 sum. Might cause corrupted repository. Aborting load." );
		return 1;
	}
	$command = '';
	$tmpfile = toPath(tempnam(rtrim(TEMP_DIR,'/'), "svn"));
	if ( isWindows() ) {
		if ( ! gunzipInternal($file,$tmpfile) ) fatal("Could not extract file $file");
		$command = "$loadcommand < $tmpfile";
	} else {
		$command = getCommand('gunzip') . " -c $file | $loadcommand";
	}
	$return = 0;
	debug("Executing: $command");
	exec( $command, $output, $return);
	deleteFile( $tmpfile );
	return $return;
}

/**
 * Gunzip file using php functions
 * Does not remove original file
 * @return bytes read (uncompressed) if successful, meaning $tofile is updated, false on error
 */
function gunzipInternal($compressedfile, $tofile) {
	$fp = fopen($tofile, "w");
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
	return $sum;   
}

/**
 * Compress file with gzip.
 * @param originalfile Full path of uncompressed file.
 * @param tofile Full path of target file, must be different than original.
 * @return bytes written (uncompressed) if successful, meaning there is now another file, false on error
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
	return $sum;
}
?>
