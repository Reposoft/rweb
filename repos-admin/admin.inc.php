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

// the old properties file concept
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
	}
	return getAdministratorEmailDefault();
}

function getAdministratorEmailDefault() {
	// user server's administrator email if it contans @
	if (isset($_SERVER['SERVER_ADMIN']) && strstr($_SERVER['SERVER_ADMIN'],'@')) return $_SERVER['SERVER_ADMIN'];
	// disable email
	return false;
}

function getAdminLocalRepo() {
	// old config structure
	return _getConfig('local_path');
}

function getAdminUserFile() {
	// TODO
	// sadly we can't read the apache configuration entries set for mod_dav_svn in the repository
	// old config structure
	return _getConfig('admin_folder')._getConfig('users_file');
}

function getAdminAccessFile() {
	// TODO
	// old config structure
	//return _getConfig('admin_folder')._getConfig('access_file');
	// from repos-web
	return getAccessFile();
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
