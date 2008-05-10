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

// --- configuration ---

/**
 * Config value getter
 * @param key the configuration value key, as in repos.properties
 * @return the value corresponding to the specified key. False if key not defined.
 */ 
function getConfig($key) {
	// temporary selfcheck
	if ($key=='repo_url') trigger_error("Use getRepository to get the URL");
	if ($key=='repos_web') trigger_error("Use getWebapp to get web root URL");
	//
	return _getConfig($key);
}

function _getConfig($key) {
	static $_repos_config = null;
	if ($_repos_config == null) {
		$_repos_config = parse_ini_file( _getPropertiesFile(), false );
	}
	if (isset($_repos_config[$key]))
		return ($_repos_config[$key] );
	return false;
}

/**
 * Resolves document folder, primarily from DOCUMENT_ROOT server variable,
 * secondarily as the parent folder of this file's folder.
 */
function getDocumentRoot() {
	$docroot = dirname(dirname(__FILE__));
	if (isset($_SERVER['DOCUMENT_ROOT'])) $docroot = $_SERVER['DOCUMENT_ROOT'];
	return $docroot.'/';
}

/**
 * Gets the path to the host administration folder.
 * @return the path to read configuratio file from, usually the same as admin_folder (from properties file in that folder)
 */
function getAdminFolder() {
	return getAdminFolderDefault();
}

function getAdminFolderDefault() {
	return getParent(getDocumentRoot()).'admin/';
}

function _getPropertiesFile() {
	$propertiesFile =  getAdminFolder().'repos.properties';
	if (!file_exists($propertiesFile)) {
		trigger_error("Repos configuration file $propertiesFile not found.");
		exit;
	}
	return $propertiesFile;
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
function isRepository($localPath) {
	if (!file_exists($localPath))
		return false;
	$command = new Command('svnlook');
	$command->addArgOption('uuid', $localPath);
	if ($command->exec()) return false;
	return strlen(array_pop($command->getOutput())) > 0;	
}
 */

/**
 * The same HEAD revision number must be used thorughout backup, or a concurrent transaction could cause invalid backup
 * @return revision number integer
function getHeadRevisionNumber($repository) {
	$command = System::getCommand("svnlook") . " youngest $repository";
	$output = array();
	$return = 0;
	$rev = (int) exec($command, $output, $return);
	if ($return!=0)
		trigger_error("Could not get revision number using $command", E_USER_ERROR);
	return $rev;
}
 */

// --- helper functions ---

/**
 * Send message to the administrator whose address is specified in repos.properties
DELETE UNUSED
function notifyAdministrator($text) {
	$address = getConfig('administrator_email');
	error("Did not notifyAdministrator. Method not implemented.");
}
 */
?>
