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
define('REPOS_WEB_LOCAL', dirname(__FILE__) . '/repos/');

// disable caching in this page because it redirects
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0 disable caching
header("Pragma: no-cache");

if (isset($_GET['logout'])) {
	// http logout should be done from root url (or parent url to all pages that need authentication)
	require(REPOS_WEB_LOCAL . 'account/logout/index.php');
	exit;
}

if (isset($_GET['login'])) {
	// do exactly the same thing as the repos login, but from this url
	require(REPOS_WEB_LOCAL . 'account/login/index.php');
	exit;
}

// show start page
if (file_exists(dirname(__FILE__).'/start.html')) {
	header("Location: /start.html");
} else {
	header("Location: /start-default.html");
}