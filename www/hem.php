<?php
require( dirname(__FILE__) . "/conf/authentication.inc.php" );

/**
 * Get a user's home directory of a repository
 */
function getHomeDir($repository) {
	return $repository . '/' . getReposUser();
}

/**
 * Get the starting point for the standard user
 */
function getDefaultRepositoryHomeDir() {
	return getHomeDir( getConfig('repo_url') );
}

var $home = getDefaultRepositoryHomeDir();
header("Location: " . $home);

?>