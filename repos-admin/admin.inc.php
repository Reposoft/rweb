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

function getAdministratorEmail() {
	if (isset($_SERVER['REPOS_ADMIN_EMAIL'])) {
		// allow outgoing email to be disabled
		if (!$_SERVER['REPOS_ADMIN_EMAIL']) return false;
		// custom email overrides apache administrator email
		return $_SERVER['REPOS_ADMIN_EMAIL'];
	} else if (isset($_SERVER['SERVER_ADMIN']) && strstr($_SERVER['SERVER_ADMIN'],'@')) {
		// server configuration
		return $_SERVER['SERVER_ADMIN'];
	} else {
		// disable email
		return false;
	}
}

function getAdminUserFile() {
	// TODO
	// sadly we can't read the apache configuration entries set for mod_dav_svn in the repository
}

function getAdminAccessFile() {
	// TODO
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

?>
