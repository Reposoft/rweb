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
	'requiredFiles' => 'Checking configuration paths',
	'dependencies' => 'Required command line tools',
	'repository' => 'Checking local repository',
	'requiredUrls' => 'Checking URLs',
	'debug' => 'Debug info'
	);
// validating configuration
$links = array(
	'logout.php' => 'Log out',
	'../admin/configuration.php' => 'System configuration help',
	'../admin/' => 'Administration'
	);
$requiredConfig = array(
	'repos_web' => 'The url of this website',
	'administrator_email' => 'Administrator E-mail',
	'repo_url' => 'Repoisitory root',
	'local_path' => 'Local path of repository',
	'admin_folder' => 'Administration folder',
	'users_file' => 'File for usernames and passwords',
	'backup_folder' => 'Local path for storage of backup'
	);
$requiredFiles = array(
	getConfig('admin_folder') . DIRECTORY_SEPARATOR . getConfig('users_file') => 'File for usernames and passwords',
	getConfig('admin_folder') . DIRECTORY_SEPARATOR . getConfig('access_file') => 'File for subversion access control',
	getConfig('admin_folder') . DIRECTORY_SEPARATOR . getConfig('export_file') => 'File for repository export paths',
	getConfig('backup_folder') => 'Local path for storage of backup'
	);
$dependencies = array(
	'svn' => '--version',
	'svnlook' => '--version',
	'svnadmin' => '--version',
	'gzip' => '--version',
	'gunzip' => '--version'
);
$repository = array(
	getCommand('svnlook') . ' youngest ' . getConfig('local_path') => "Local path contains repository revision: "
);
$rurl = getConfig('repo_url');
$aurl = str_replace("://","://" . getReposUser() . ":" .  getReposPass() . "@", getConfig('repo_url'));
$lurl = ereg_replace("://[\w\.-_]+/","://localhost/", getConfig('repo_url'));
$requiredUrls = array( 
	getConfig('repos_web') => 'Acces to static contents ' . getConfig('repos_web'),
	$rurl => 'Anonymous acces to the repository ' . getConfig('repo_url'),
	$aurl => "Access to repository with current authenticatied user (" . getReposUser() . ")",
	$lurl => "Access to repository using localhost"
	);
print_r($requiredUrls);

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
<link href="../css/repos-standard.css" rel="stylesheet" type="text/css">
</head>

<body>
<?
}

function html_end() {
	echo "</body></html>";
}

function line_start($text='') {
	echo "<p>";
	if (strlen($text)>0) {
	?><span style="width: 400px; overflow:hidden; border-bottom: thin dotted #CCCCCC; "><? echo $text ?></span><?
	}
}

function line_end() {
	echo "</p>\n";
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
	line_start();
	foreach ( $links as $url=>$name ) {
		echo "<a href=\"$url\">$name</a> &nbsp; ";
	}
	line_end();
}

function requiredConfig() {
	global $requiredConfig;
	foreach ($requiredConfig as $key => $descr) {
		$val = getConfig($key);
		line_start("$descr ($key): ");
		if ($val === false)
			sayFailed("Missing");
		else
			sayOK($val);
		line_end();
	}
}

function requiredFiles() {
	global $requiredFiles;
	line_start("Running as user: ");
	passthru( 'whoami' );
	line_end();
	foreach ($requiredFiles as $key => $descr) {
		$exists = file_exists($key);
		line_start("$descr ($key): ");
		if ( ! $exists ) {
			sayFailed("Missing");
		} else {
			sayOK("Exists");
			echo " writable: ";
			$writable = is_writable($key);	
			if ( ! $writable)
				sayFailed("No");
			else
				sayOK("Yes");
		}
		line_end();
	}
}

function dependencies() {
	global $dependencies;
	$retval = 0;
	foreach ( $dependencies as $cmd => $check ) {
		$output = array();
		$run = getCommand($cmd);
		line_start("$cmd ($run): ");
		exec( "$run $check", $output, $retval );
		//print_r($output);
		//echo "$retval";
		if ($retval==0)
			sayOK( $output[0] );
		else
			sayFailed();
		line_end();
	}
}

function repository() {
	global $repository;
	foreach ( $repository as $command => $descr ) {
		line_start($descr);
		$result = exec( $command, $out, $ret );
		if ($ret == 0)
			sayOK( $result );
		else
			sayFailed( $result );
		line_end($descr);
	}
}

function requiredUrls() {
	global $requiredUrls;
	foreach ( $requiredUrls as $url => $descr ) {
		line_start($descr);
		$up = fopen($url, "r");
		if ($up) {
			sayOK();
			fclose($up);
		} else {
			sayFailed();
		} line_end();
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