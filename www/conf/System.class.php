<?php
/**
 * Controls access to the filesystem and server environment.
 * Unlike repos.properties.php, these functions do not depend on configuration.
 * 
 * Some standard PHP functions should not be used in repos, except through this class.
 * - tempnam
 * - unlink
 * - exec/passthru (use Command class instead)
 * 
 * @package conf
 */

define('USRBIN', '/usr/bin/');

// ----- string helper functions that should have been in php -----
function strBegins($str, $sub) { return (substr($str, 0, strlen($sub)) === $sub); }
function strEnds($str, $sub) { return (substr($str, strlen($str) - strlen($sub)) === $sub); }
function strContains($str, $sub) { return (strpos($str, $sub) !== false); }
function strAfter($str, $sub) { return (substr($str, strpos($str, $sub) + strlen($sub))); }


// ----- global functions, logic for the repos naming conventions for path -----

/**
 * A path is a String of any length, not containing '\'.
 * Windows paths must be normalized using toPath.
 * @package conf
 */
function isPath($path) {
	if (!is_string($path)) {
		trigger_error("Path $path is not a string.", E_USER_ERROR);
		return false;
	}
	if (strContains($path, '\\')) {
		trigger_error("Path $path contains backslash. Use toPath(path) to convert to generic path.", E_USER_ERROR);
		return false;
	}
	if (strContains(str_replace('://','',$path), '//')) {
		trigger_error("Path $path contains double slashes.", E_USER_ERROR);
		return false;
	}
	return true;
}

/**
 * Converts filesystem path to path that works on all OSes, with same encoding as command line.
 * Windows paths are converted from backslashes to forward slashes, and from UTF-8 to ISO-8859-1 (see toShellEncoding).
 * If a path does not use the OS encoding, functions like file_exists will only work with ASCII file names. 
 * @param String $path path that might contain backslashes
 * @return String the same path, but with forward slashes
 * @package conf
 */
function toPath($path) {
	return System::toShellEncoding(strtr($path, '\\', '/'));
}

/**
 * Absolute paths start with '/' or 'protocol://', on Windows only, 'X:/'.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is absolute, false if not
 * @package conf
 */
function isAbsolute($path) {
	if (!isPath($path)) trigger_error("'$path' is not a valid path", E_USER_ERROR);
	if (strBegins($path, '/')) return true;
	if (System::isWindows() && ereg('^[a-zA-Z]:/', $path)) return true;
	return ereg('^[a-z]+://', $path)!=false;
}

/**
 * Relative paths are those that are not absolute, including empty strings.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is relative, false if not
 * @package conf 
 */
function isRelative($path) {
	return !isAbsolute($path);
}

/**
 * Files are relative or absolute paths that do not end with '/'.
 * The actual filename can be retreived using getParent($path).
 * @param String $path the file system path or URL to check
 * @return boolean true if path is a file, false if not 
 * @package conf
 */
function isFile($path) {
	if (!isPath($path)) trigger_error("'$path' is not a valid path");
	return !strEnds($path, '/');
}

/**
 * Folders are relative or absolute paths that _do_ end with '/'
 *  or (on Windows only) '\'
 * To check if a URL with no tailing slash is a folder, use login_getResourceType.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is a folder, false if not 
 * @package conf
 */
function isFolder($path) {
	return !isFile($path);
}

/**
 * @param String $path the file system path or URL to check
 * @return The parent folder if isFolder($path), the folder if isFile($path), false if there is no parent
 * @package conf
 */
function getParent($path) {
	if (strlen($path)<1) return false;
	$c = substr_count($path, '/');
	if ($c < 2 || ($c < 4 && strContains($path, '://') && !($c==3 && !strEnds($path, '/')))) return false; 
	$f = substr($path, 0, strrpos(rtrim($path,'/'), '/'));
	if (strlen($f)==0 && isRelative($path)) return $f;
	return $f.'/';
}

/**
 * Non configurable global functions.
 * 
 * It is not allowed for code outside this file to do
 * any of: unlink(x), rmdir(x), touch(x), fopen(x, 'a' or 'w')
 *
 * @static
 * @package conf
 */
class System {
	
	/**
	 * @return true if the web server is running windows
	 */
	function isWindows() {
		return ( substr(PHP_OS, 0, 3) == 'WIN' );
	}

	/**
	 * @return newline character for this OS, the one used by subversion with "svn:eol-style native".
	 */
	function getNewline() {
		if (System::isWindows()) return "\r\n";
		else return "\n";
	}
	
	/**
	 * Manages the common temp dir for repos-php. Temp is organized in subfolders per operation.
	 * This method returns an existing temp folder; to get a new empty folder use {@link getTempFolder}.
	 * @param String $subfolder optional name of a subfolder in the application temp folder, no slashes
	 * @return absolute path to temp, or the subfolder of temp, with trailing slash
	 */
	function getApplicationTemp($subfolder=null) {
		// Get temporary directory
		$systemp = System::_getSystemTemp();
		if (is_writable($systemp) == false) die ('Error. Can not write to temp foloder.');
		// Create a repos subfolder, allow multiple repos installations on the same server
		$appname = 'r'.trim(base64_encode(dirname(dirname(__FILE__))),'=');
		$tmpdir = $systemp . $appname;
		if (!file_exists($tmpdir)) {
			mkdir($tmpdir);
		}
		if ($subfolder) {
			$tmpdir .= '/' . $subfolder;
			if (!file_exists($tmpdir)) {
				mkdir($tmpdir);
			}
		}
		return toPath($tmpdir) . '/';
	}
	
	/**
	 * Return a new empty temporary file
	 * @param String $subfolder (optional) category (like 'upload'), subfolder to temp where the file will be created.
	 */
	function getTempFile($subfolder=null) {
		return strtr(tempnam(rtrim(System::getApplicationTemp($subfolder),'/'), ''), "\\", '/');
	}
	
	/**
	 * Return a new, empty, temporary folder.
	 * @param String $subfolder optional category (like 'upload')
	 * @see getApplicationTemp
	 */
	function getTempFolder($subfolder=null) {
       // Use PHP's tmpfile function to create a temporary
       // directory name. Delete the file and keep the name.
       $tempname = System::getTempFile($subfolder);
       $tempname = toPath($tempname);
       if (!$tempname)
               return false; // TODO trigger error

       if (!unlink($tempname))
               return false; // TODO trigger error

       // Create the temporary directory and returns its name.
       if (mkdir($tempname))
               return $tempname.'/';

       return false;
	}
	
	// ------ functions to keep scripts portable -----
	
	/**
	 * Converts a string from internal encoding to the encoding used for file names and commands.
	 * @param String $string the value with internal encoding (same as no encoding)
	 * @return String the same value encoded as the OS expects it on the command line
	 * @deprecated use System::toShellEncoding
	 */
	function toShellEncoding($string) {
		if (System::isWindows()) {
			return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
		}
		return $string;
	}
	
	/**
	 * Get the execute path of command line operations supported by repos.
	 * @param String $commandName Command name, i.e. 'svnadmin'.
	 * @return String Full command with path, false if the command shouldn't be needed in current OS.
	 * Error message starting with 'Error:' if command name is not supported.
	 * Get the execute path of the subversion command line tools used for repository administration
	 * @param Command name, i.e. 'svnadmin'.
	 * @return Command line command, false if the command shouldn't be needed in current OS. Error message starting with 'Error:' if command name is not supported.
	 */
	function getCommand($command) {
		if ($c = System::_getSpecialCommand($command)) {
			return $c;
		}
		$w = System::isWindows();
		switch($command) {
			case 'svn':
				return ( $w ? 'svn' : USRBIN . 'svn' );
			case 'svnlook':
				return ( $w ? 'svnlook' : USRBIN . 'svnlook' );
			case 'svnadmin':
				return ( $w ? 'svnadmin' : USRBIN . 'svnadmin' );
			case 'gzip':
				return ( $w ? false : USRBIN . 'gzip' );
			case 'gunzip':
				return ( $w ? false : USRBIN . 'gunzip' );
			case 'whoami':
				return 'whoami';
			case 'env':
				return ( $w ? 'set' : USRBIN . 'env' );
			case 'du':
				return ( $w ? false : USRBIN . 'du' );
			case 'curl':
				return ( $w ? 'curl' : USRBIN . 'curl' );
			case 'wget':
				return ( $w ? 'wget' : USRBIN . 'wget' );
		}
		return false;
	}
	
	function _getSpecialCommand($name) {
		if ($name == 'htpasswd') return 'C:/srv/ReposServer/apache/bin/htpasswd.exe';
		return false;
	}
	
	// ----- file system helper functions ------
	
	/**
	 * Deletes a folder and all contents. No other scripts in repos may call the 'unlink' method.
	 * Removes the folder recursively if it is in one of the allowed locations,
	 * such as the temp dir and the repos folder.
	 * Note that the path should be encoded with the local shell encoding, see toPath.
	 * @param String $folder absolute path, with tailing slash like all folders.
	 *  Valid path is either inside the repos folder or in the repos temp location
	 */
	function deleteFolder($folder) {
		System::_authorizeFilesystemModify($folder);
		if (!isFolder($folder)) {
			trigger_error("Path \"$folder\" is not a folder.", E_USER_ERROR); return false;
		}
		
		if (!file_exists($folder) || !is_dir($folder)) {
			trigger_error("Path \"$folder\" does not exist.", E_USER_ERROR); return false;
		}
		if (!is_readable($folder)) {
			trigger_error("Path \"$folder\" is not readable.", E_USER_ERROR); return false;
		}
		if (!is_writable($folder) && !System::_chmodWritable($folder)) {
			trigger_error("Path \"$folder\" is not writable.", E_USER_ERROR); return false;
		}
		else {
			$handle = opendir($folder);
			while (false !== ($item = readdir($handle))) {
				if ($item != '.' && $item != '..') {
					$path = $folder.$item;
					if(is_dir($path)) {
						System::deleteFolder($path.'/');
					} else {
						System::deleteFile($path);
					}
				}
			}
			closedir($handle);
			if(!rmdir($folder)) {
				trigger_error("Could not remove folder \"$folder\".", E_USER_ERROR); return false;
			}
			return true;
		}
	}
	
	/**
	 * replaces touch().
	 * @deprecated use System::createFile
	 */
	function createFile($absolutePath) {
		System::_authorizeFilesystemModify($absolutePath);
		if (!isFile($absolutePath)) {
			trigger_error("Path \" $absolutePath\" is not a valid file name.", E_USER_ERROR); return false;
		}
		return touch($absolutePath);
	}
	
	/**
	 * replaces mkdir().
	 * @deprecated use System::createFolder
	 */
	function createFolder($absolutePath) {
		System::_authorizeFilesystemModify($absolutePath);
		if (!isFolder($absolutePath)) {
			trigger_error("Path \" $absolutePath\" is not a valid folder name.", E_USER_ERROR); return false;
		}
		return mkdir($absolutePath);
	}
	
	/**
	 * replaces unlink().
	 * @param String $file absolute path to file
	 */
	function deleteFile($file) {
		System::_authorizeFilesystemModify($file);
		if (!isFile($file)) {
			trigger_error("Path \" $file\" is not a file.", E_USER_ERROR); return false;
		}
		if (!file_exists($file)) {
			trigger_error("Path \" $file\" does not exist.", E_USER_ERROR); return false;
		}
		if (!is_readable($file)) {
			trigger_error("Path \" $file\" is not readable.", E_USER_ERROR); return false;
		}
		if (!is_writable($file) && !System::_chmodWritable($file)) {
			trigger_error("Path \" $file\" is not writable.", E_USER_ERROR); return false;
		}
		return unlink($file);
	}
	
	/**
	 * Instead of createFile() and fopen+fwrite+fclose.
	 */
	function createFileWithContents($absolutePath, $contents, $convertToWindowsNewlineOnWindows=false, $overwrite=false) {
		if (!isFile($absolutePath)) {
			trigger_error("Path $absolutePath is not a file."); return false;
		}
		if (file_exists($absolutePath) && !$overwrite) {
			trigger_error("Path $absolutePath already exists. Delete it first."); return false;
		}
		if ($convertToWindowsNewlineOnWindows) {
			$file = fopen($absolutePath, 'wt');	
		} else {
			$file = fopen($absolutePath, 'w');
		}
		$b = fwrite($file, $contents);
		fclose($file);
		return $b;
	}
	
	/**
	 * Replaces chmod 0777, and is used internally before deleting files or folders.
	 * Only allowes chmodding of folders that are expected to be write protected, like .svn. 
	 * @return false if it is not allowed to chmod the path writable
	 */
	function _chmodWritable($absolutePath) {
		// why would we
		if (strContains($absolutePath, '/.svn')) return chmod($absolutePath, 0777);
		if (strBegins($absolutePath, System::_getSystemTemp())) return chmod($absolutePath, 0777);
		return false;
	}
	
	/**
	 * @todo
	 *
	 * @param unknown_type $absolutePath
	 */
	function chmodWebRuntimeWritable($absolutePath) {
		// what if we are running in CLI mode? we probably are not.
	}
	
	/**
	 * @todo
	 *
	 * @param unknown_type $absolutePath
	 */
	function chmodWebGroupWritable($absolutePath) {
		
	}
	
	/**
	 * It is considered a serious system error if a modify path is invalid according to the internal rules.
	 * Therefore we throw an error and do exit.
	 */
	function _authorizeFilesystemModify($path) {
		if (!isAbsolute($path)) {
			trigger_error("Security error: local write not allowed in \"$path\". It is not absolute.", E_USER_ERROR);
		}
		$tmp = System::_getSystemTemp(); // segfault if inside strBegins
		if (strBegins($path, $tmp)) {
			return true;
		}
		if (strContains($path, 'repos')) {
			return true;
		}
		// assume that the web server host is in some server folder
		if (strBegins($path, toPath(dirname(dirname(dirname(dirname(__FILE__))))))) {
			return true;
		}
		trigger_error("Security error: local write not allowed in \"$path\". It is not a temp or repos dir.", E_USER_ERROR);
	}
	
	/**
	 * Platform independen way of getting the server's temp folder.
	 * @return String absolute path, folder, existing
	 */
	function _getSystemTemp() {
		static $tempfolder = null;
		if (!is_null($tempfolder)) return $tempfolder;
		$type = '';
		if (getenv('TMP')) {
			$type = 'TMP';
			$tempdir = getenv('TMP');
		} elseif (getenv('TMPDIR')) {
			$type = 'TMPDIR';
			$tempdir = getenv('TMPDIR');
		} elseif (getenv('TEMP')) {
			$type = 'TEMP';
			$tempdir = getenv('TEMP');
		} else {
			$type = 'tempnam';
			// suggest a directory that does not exist, so that tempnam uses system temp dir
			$doesnotexist = 'dontexist'.rand();
			$tmpfile = tempnam($doesnotexist, 'emptytempfile');
			if (strpos($tmpfile, $doesnotexist)!==false) trigger_error("Could not get system temp, got: ".$tmpfile);
			$tempdir = dirname($tmpfile);
			unlink($tmpfile);
			if (strlen($tempdir)<4) trigger_error("Attempted to use tempnam() to get system temp dir, but the result is: $tempdir", E_USER_ERROR);
		}
	
		if (empty($tempdir)) { trigger_error('Can not get the system temp dir', E_USER_ERROR); }
		
		$tempdir = rtrim(toPath($tempdir),'/').'/';
		if (strlen($tempdir) < 4) { trigger_error('Can not get the system temp dir, "'.$tempdir.'" is too short. Method: '.$type, E_USER_ERROR); }
		
		$tempfolder = $tempdir;
		return $tempfolder;
	}
}

?>
