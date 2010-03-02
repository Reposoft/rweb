<?php
/**
 * Repos admin authorization (c) 2008 Staffan Olsson repos.se
 * Restrict script access to (admin host OR admin user)
 * Passes silently if request is autorized, uses trigger_error if access is denied
 */

// First set the server environment variable name used to authorize admin access
// Value of this variable can be derived from client ip, filters or rewrite rules
// for example a RewriteCond %{REMOTE_USER} ^mrpresiden$

	// @deprecated Old environment variable name, will not be supported in repos 1.3+
	if (array_key_exists('IS_ADMIN_CLIENT', $_SERVER)) {
		define('REPOS_ADMIN_PARAMETER', 'IS_ADMIN_CLIENT');
	}

// Preferred server environment entry ReposAdminAuthorized
if (!defined('REPOS_ADMIN_PARAMETER')) define('REPOS_ADMIN_PARAMETER', 'ReposAdminAuthorized');

// Server authorization has priority both for allow and deny
if (array_key_exists(REPOS_ADMIN_PARAMETER, $_SERVER)) {
	if ($_SERVER[REPOS_ADMIN_PARAMETER]) {
		// autorized, resume execution in caller script	
	} else {
		header('HTTP/1.0 401 Unauthorized');
		trigger_error("Access denied by server configuration.", E_USER_WARNING); // expecting exit
	}
} else if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
	// By default allow local access if admin parameter is not set
	// Authorized, resume execution in caller script
} else {
	// Not authrized in server, use repos administration access check
	require_once( dirname(__FILE__).'/reposweb.inc.php' );
	require_once( ReposWeb.'account/login.inc.php' );
	
	// The repository url that can be used to verify access level
	$administrationUrl = getRepository().'/administration/';
	
	// Use repos-web login logic
	if (isLoggedIn() && verifyLogin($administrationUrl)) {
		// Authorized, resume execution in caller script
	} else {
		header('HTTP/1.0 401 Unauthorized');
		// TODO prompt for authentication?
		trigger_error("This resource is for administators only. "
			."Requests must be authorized by server configuration or the authenticated user must have access to $administrationUrl",
			E_USER_WARNING); // expecting exit
	}
}

// account administration should be allowed to ask if request is authenticated
// even if repos web authentication is not included above 
if (!function_exists('isLoggedIn')) {
	function isLoggedIn() {
		return false;
	}
}

?>
