<?php
/**
 * Debug and visualize repos configuration
 */

// default configuration includes, the way they should be referenced in php files
require( dirname(__FILE__) . '/authentication.inc.php' );
require( dirname(__FILE__) . '/repos.properties.php' );

// configuration index settings
$sections = array(
	'links' => 'Links',
	'requiredConfig' => 'Required configuration entries',
	'debug' => 'Debug info'
	);
// validating configuration
$links = array(
	'logout.php' => 'Log out',
	'configuration.php' => 'Configuration help'
	);
$requiredConfig = array(
	'administrator_email' => 'Administrator E-mail',
	'repo_url' => 'Repoisitory root',
	'local_path' => 'Local path of repository',
	'admin_folder' => 'Administration folder',
	'users_file' => 'File for usernames and passwords',
	'backup_folder' => 'Local path for storage of backup'
	);
$dependencies = array(
	'svn' => '',
	'svnlook' => '',
	'svnadmin' => '',
);
$requiredUrls = array(
	);

html_start();	
sections();
html_end();

// --- layout ---
function sections() {
	global $sections;
	foreach ( $sections as $fnc=>$name ) {
		echo "<h2>$name</h2>\n";
		call_user_func ($fnc);
	}
}

function html_start() {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Repos Configuration Info</title>
</head>

<body>
<?
}

function html_end() {
	echo "</body></html>";
}

// --- helper functions ---

function sayOK($msg = 'OK') {
	?><span style="color:#006600; padding-left:5px; padding-right:5px;"><strong><? echo $msg ?></strong></span><?
}

function sayFailed($msg = 'Failed') {
	?><span style="color:#990000; padding-left:5px; padding-right:5px;"><strong><? echo $msg ?></strong></span><?
}

// --- sections' presentation ---

function links() {
	global $links;
	echo "<p>";
	foreach ( $links as $url=>$name ) {
		echo "<a href=\"$url\">$name</a> &nbsp; ";
	}
	echo "</p>\n";
}

function requiredConfig() {
	global $requiredConfig;
	foreach ($requiredConfig as $key => $descr) {
		$val = getConfig($key);
		echo "<p>$descr ($key): ";
		if ($val === false)
			sayFailed("Missing");
		else
			sayOk($val);
		echo "</p>";
	}
}

// Debug output, does nothing
function debug() {
	echo "<pre>\n";
	echo "==== Test retrieval of credentials ===";
	echo "\nUsername = ";
	//echo $repos_authentication['user'];
	echo getReposUser();
	echo "\nPassword = ";
	echo getReposPass();
	//echo $repos_authentication['pass'];
	echo "\nBASIC string = ";
	echo getReposAuth();
	//echo $repos_authentication['auth'];
	echo "\n";
	// Display info about current repos configuration
	echo "\n==== Configuration file ===\n";
	global $repos_config;
	print_r($repos_config);
	echo "\n==== Server variables ===\n";
	print_r($_SERVER);
	echo "</pre>\n";
}

?>