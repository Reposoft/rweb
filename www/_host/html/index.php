<?php
/**
 * Redirects to server start page
 *
 * if ?logout is set the script clears HTTP auth credentials before logout
 *  (using the realm from the config file)
 *
 * This script is intended to be placed in server root,
 * for logout to be effective with all URLs on the server.
 */

$_path = dirname(dirname(dirname(__FILE__))).'/';
$_webapp = isset($_SERVER['REPOS_WEBAPP']) ? $_SERVER['REPOS_WEBAPP'] : '/repos-web/';

// this file might have been copied to server root
if (!file_exists($_path.'conf/repos.properties.php')) {
	if (substr_count($_webapp, '/') > 3) $_webapp = substr($_webapp, strpos($_webapp,'/',8)); // remove http://hostname/
	$_path = dirname(__FILE__) . $_webapp;
}

// disable caching in this page because it redirects
header('Cache-Control: no-cache');
header('Pragma: no-cache'); // HTTP/1.0

if (isset($_GET['logout'])) {
	// http logout should be done from root url (or parent url to all pages that need authentication)
	require($_path.'account/logout/index.php');
	exit;
}

if (isset($_GET['login'])) {
	// do exactly the same thing as the repos login, but from this url
	require($_path.'account/login/index.php');
	exit;
}

// show start page
if (file_exists(dirname(__FILE__).'/home/index.html')) {
	header("Location: /home/");
} elseif (file_exists(dirname(__FILE__).'/start.html')) {
	header("Location: /start.html");
} else {
	header("Location: ".$_webapp);
}
