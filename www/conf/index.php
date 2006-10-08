<?php
/**
 * Debug and visualize repos configuration
 */

// default configuration includes, the way they should be referenced in php files
require_once( dirname(__FILE__) . '/repos.properties.php' );
require_once( dirname(dirname(__FILE__)) . '/account/login.inc.php' );

// configuration index settings
$sections = array(
	'links' => 'Links',
	'requiredConfig' => 'Required configuration entries',
	'requiredFiles' => 'Checking configuration paths',
	'dependencies' => 'Required command line tools',
	'repository' => 'Checking local repository',
	'requiredUrls' => 'Checking URLs',
	'localeSettings' => 'Checking locales for the web server\'s command line',
	'debug' => 'Debug info'
	);
// validating configuration
$links = array(
	'../logout.php' => 'Log out',
	'../admin/configure/' => 'System configuration help',
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
	getConfig('admin_folder') . getConfig('users_file') => 'File for usernames and passwords',
	getConfig('admin_folder') . getConfig('access_file') => 'File for subversion access control',
	getConfig('admin_folder') . getConfig('export_file') => 'File for repository export paths',
	getConfig('backup_folder') => 'Local path for storage of backup'
	);
$dependencies = array(
	'svn' => '--version',
	'svnlook' => '--version',
	'svnadmin' => '--version',
	'gzip' => '--version',
	'gunzip' => '--version',
	'whoami' => '--version'
);
$repository = array(
	getCommand('svnlook') . ' youngest ' . getConfig('local_path') => "Local path contains repository revision: "
);


// checking urls needed for repository access
$rurl = getRepository();
$realm = $rurl;
if (strlen($realm)<1) trigger_error('repo_realm not set in configuration', E_USER_WARNING);
$aurl = str_replace("://","://" . getReposUser() . ":" .  _getReposPass() . "@", getRepository());
$uurl = $aurl.'/'.getReposUser();
$lurl = ereg_replace("://[^/<>[:space:]]+[[:alnum:]]/","://localhost/", getRepository());
if ( getWebapp()==getRepository() )
	echo "Warning: repos_web is same as repository - mixing static resources and repository";
	
$requiredUrls = array( getWebapp() => 'Acces to static contents ' . getRepository());
$requiredUrls[$rurl] = 'Anonymous acces to the repository ' . getRepository();
$requiredUrls[$aurl] = "Access to repository with current authenticatied user (" . getReposUser() . ")";
$requiredUrls[$uurl] = "Access to user folder in repository (" . getReposUser() . ")";
$requiredUrls[$lurl] = "Access to repository using localhost";

// run the diagnostics page
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

function html_start($title='Repos configuration info') {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $title ?></title>
<link href="../style/global.css" rel="stylesheet" type="text/css">
<link href="../style/docs.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php
}

function html_end() {
	echo "</body></html>";
}

function line_start($text='') {
	echo "<p>";
	if (strlen($text)>0) {
	?><span style="width: 400px; overflow:hidden; border-bottom: thin dotted #CCCCCC; "><?php echo $text ?></span><?php
	}
}

function line_end() {
	echo "</p>\n";
}

// --- helper functions ---

function sayOK($msg = 'OK') {
	?><span style="color:#006600; padding-left:5px; padding-right:5px;"><strong><?php echo $msg ?></strong></span><?php
}

function sayFailed($msg = 'Failed') {
	?><span style="color:#990000; padding-left:5px; padding-right:5px;"><strong><?php echo $msg ?></strong></span><?php
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
		$val = _getConfig($key);
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
	passthru( getCommand('whoami') );
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

function localeSettings() {
	exec('locale', $localeOutput);
	$locales = Array();
	foreach ($localeOutput as $locale) {
		list($env, $val) = explode('=', $locale);
		line_start($env);
		if (strpos($val, "UTF-8")===false) {
			sayFailed("$val (not UTF-8, so i18n not supported in svn commands)");
		} else {
			sayOK();
		}
		line_end();
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
	echo str_repeat("*", strlen(_getReposPass()));
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
	echo "\n==== Command line environment ===\n";
	$output = repos_runCommand('env','');
	print_r($output);
	echo "</pre>\n";
}

?>