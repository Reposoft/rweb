<?php
require( dirname(__FILE__) . "/conf/authentication.inc.php" );

/**
 * Get a user's home directory of a repository
 */
function getHomeDir($repository) {
	return $repository . '/' . getReposUser() . '/trunk/';
}

/**
 * Get the starting point for the standard user
 */
function getDefaultRepositoryHomeDir() {
	return getHomeDir( getConfig('repo_url') );
}

$home = getDefaultRepositoryHomeDir();
header("Location: " . $home);

?>