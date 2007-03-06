<?php
/**
 * The test/setup/ URL used from web integratino tests to get to the starting point.
 * 
 * ?repos => repos webapp root, normally /repos/
 * ?admin => repos administration start
 * ?start => Repos startpage for autenticated user
 * ?demoproject => Demoproject trunk
 * ?public => 'public' folder in demoproject
 * (default) testuser trunk, which is normally only updated by tests
 * 
 * @package test
 */

if (array_key_exists('repos', $_GET)) {
	header("Location: /repos/");
	exit;
}

if (array_key_exists('admin', $_GET)) {
	header("Location: /repos/admin/");
	exit;
}

if (array_key_exists('start', $_GET)) {
	header("Location: /repos/open/start/");
	exit;
}

if (array_key_exists('demoproject', $_GET)) {
	header("Location: /testrepo/demoproject/trunk/");
	exit;
}

if (array_key_exists('public', $_GET)) {
	header("Location: /testrepo/demoproject/trunk/public/");
	exit;
}

if (array_key_exists('reset', $_GET)) {
	header("Location: /repos/test/reset/testrepo/");
	exit;
}

if (array_key_exists('resetminimal', $_GET)) {
	header("Location: /repos/test/reset/minimal/");
	exit;
}

// go to start page for repository browser tests
header("Location: /testrepo/test/trunk/");


?>