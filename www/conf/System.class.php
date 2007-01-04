<?php
/**
 * Controls access to the filesystem and server environment.
 * 
 * Some standard PHP functions should not be used in repos, except through this class.
 * - tempnam
 * - unlink
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
}

?>
