<?php
/**
 * Temporary solution to allow Repos Web access to some admin services
 * that have been tightly integrated with the core when they were the same component.
 */


require( dirname(dirname(__FILE__)).'/conf/repos.properties.php' );

if (!isset($_SERVER['IS_ADMIN_CLIENT']) || !$_SERVER['IS_ADMIN_CLIENT']) {
	// the repos-admin application can be protected using any method,
	// but here we hard code for local service calls
	trigger_error('Repos-admin services are only accessible from admin client', E_USER_ERROR);
}

define('ReposAdmin', dirname(dirname(dirname(__FILE__))).'/repos-admin/');

if (!file_exists(ReposAdmin)) {
	trigger_error('Requires the repos-admin component', E_USER_ERROR);
}

?>
