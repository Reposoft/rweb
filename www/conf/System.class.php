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

// shared functionality included from both repos.properties.php and simpletest/setup.php

// currently we simply delegate to repos.properties.php functions so that the transition can be done gradually
// TODO remove all the system functions from repos.properties.php
require_once(dirname(__FILE__).'/repos.properties.php'); // TODO remove this import

class System {
	
	/**
	 * Deletes a folder and all contents. No other scripts in repos may call the 'unlink' method.
	 *
	 * @param String $folder valid path, either inside the repos folder or in the repos temp location
	 * @static 
	 */
	function deleteFolder($folder) {
		return deleteFolder($folder);
	}
	
	function isWindows() {
		return isWindows();
	}

	/**
	 * @return newline character for this OS
	 */
	function getNewline() {
		if (System::isWindows()) return "\n\r";
		else return "\n";
	}
	
	/**
	 * Returns the path to a temporary folder for this repos installation
	 * @param String $subfolder optional name of a subfolder in the application temp folder
	 * @see getTempFolder to get a new folder inside subfolder
	 */
	function getApplicationTemp($subfolder=null) {
		return getTempDir($subfolder);
	}
	
	/**
	 * Return a new empty temporary file
	 * @param String $subfolder (optional) category (like 'upload'), subfolder to temp where the file will be created.
	 */
	function getTempFile($subfolder=null) {
		return _getTempFile($subfolder);
	}
	
	/**
	 * Return a new empty temporary folder
	 * @param String $subfolder (optional) category (like 'upload'), subfolder to temp where the file will be created.
	 */
	function getTempFolder($subfolder=null) {
		return getTempnamDir($subfolder);
	}
	
	/**
	 * Get the execute path of command line operations supported by repos.
	 * @param String $commandName Command name, i.e. 'svnadmin'.
	 * @return String Full command with path, false if the command shouldn't be needed in current OS.
	 * Error message starting with 'Error:' if command name is not supported.
	 */
	function getCommand($commandName) {
		return getCommand($commandName);
	}
}

?>
