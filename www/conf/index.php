<?php
/**
 * Debug and visualize repos configuration.
 * 
 * This page does not use the Presentation class, 
 * because it must be able to run without 3rd party libraries.
 * 
 * @author Staffan Olsson (solsson)
 * @package conf
 */

// configuration display not updated for repos 1.2
echo "Not a repos service. Contains shared scripts.";exit;

// default configuration includes, the way they should be referenced in php files
require_once( dirname(__FILE__) . '/repos.properties.php' );
require_once( dirname(__FILE__) . '/Command.class.php' );
require_once( dirname(dirname(__FILE__)) . '/open/SvnOpen.class.php' ); // To get SVN_CONFIG_DIR

// configuration index settings
$sections = array(
	'links' => 'Status of repos.properties',
	'requiredConfig' => 'Required configuration entries',
	'requiredFiles' => 'Checking configuration paths',
	'dependencies' => 'Required command line tools',
	'repository' => 'Checking local repository',
	'localeSettings' => 'Checking locales for the web server\'s command line',
	'resources' => 'Checking local system',
	'serverVariables' => 'Custom apache configuration passed on to Repos'
	// disabled becaus it contains server data // 'debug' => 'Debug info'
	);
// validating configuration
$links = array(
	'php/TestServerSettings.php' => 'php.ini',
	'../admin/configure/' => 'System configuration help',
	'../admin/' => 'Administration',
	'../test/' => 'Automated tests',
	'../' => 'startpage',
	'/?logout' => 'Log out'
	);
$requiredConfig = array(
	'repos_web' => 'The url of this website',
	'repositories' => 'Repoisitory address or addresses',
	'local_path' => 'Local path of repository',
	'admin_folder' => 'Administration folder',
	// 'users_file' => 'File for usernames and passwords',
	'backup_folder' => 'Local path for storage of backup'
	);
// TODO non required entries
$_a = getConfig('admin_folder');
$requiredFiles = array(
	SVN_CONFIG_DIR => '--svn-config-dir parameter value',
	$_a => 'Administration folder',
	getConfig('backup_folder') => 'Local path for storage of backup'
	);
if (getConfig('users_file')) $requiredFiles[$_a.getConfig('users_file')] = 'File for usernames and passwords';
if (getConfig('access_file')) $requiredFiles[$_a.getConfig('access_file')] = 'File for subversion access control';
if (getConfig('exports_file')) $requiredFiles[$_a.getConfig('exports_file')] = 'File for repository export paths';

$dependencies = array(
	'svn' => '--version --config-dir '.SVN_CONFIG_DIR,
	'svnlook' => '--version',
	'svnadmin' => '--version',
	'gzip' => '--version',
	'gunzip' => '--version',
	'curl' => '--version',
	'wget' => '--version'
//	'whoami' => '--version'
);
$repository = array(
	System::getCommand('svnlook') . ' youngest ' . getConfig('local_path') => "Local path contains repository revision: "
);
$serverVariables = array(
	'REPOS_PRIMARY' => 1,
	'IS_LOCAL_CLIENT' => 1,
	'IS_ADMIN_CLIENT' => 1
);

// run the diagnostics page
html_start();	
sections();
html_end();

// --- layout ---
$passes = 0;
$fails = 0;

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
	global $passes, $fails;
	line_start('Done. ');
	$result = "$passes passes, ".($fails+0)." fails and 0 exceptions.";
	if ($fails == 0) {
		sayOK($result);
	} else {
		sayFailed($result);
	}
	line_end();
	echo "<hr/></body></html>";
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
	global $passes;
	$passes++;
	?><span style="color:#006600; padding-left:5px; padding-right:5px;"><strong><?php echo $msg ?></strong></span><?php
}

function sayFailed($msg = 'Failed') {
	global $fails;
	$fails++;
	?><span style="color:#990000; padding-left:5px; padding-right:5px;"><strong><?php echo $msg ?></strong></span><?php
}

function saySomething($msg) {
	?><span style="color:#333333; padding-left:5px; padding-right:5px;"><strong><?php echo $msg ?></strong></span><?php
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
			sayFailed("Not set");
		else
			sayOK($val);
		line_end();
	}
}

function requiredFiles() {
	global $requiredFiles;
	line_start("Running as user: ");
	passthru( System::getCommand('whoami') );
	line_end();
	foreach ($requiredFiles as $key => $descr) {
		$exists = file_exists($key);
		line_start("$descr ($key): ");
		if ( ! $exists ) {
			sayFailed("Missing");
		} else {
			sayOK("Exists");
			saySomething(" writable: ");
			$writable = is_writable($key);	
			if ( ! $writable)
				saySomething("No");
			else
				saySomething("Yes");
		}
		line_end();
	}
}

function dependencies() {
	global $dependencies;
	$retval = 0;
	foreach ( $dependencies as $cmd => $check ) {
		$output = array();
		$run = System::getCommand($cmd);
		line_start("$cmd ($run): ");
		if (empty($run)) {
			sayOK('not supported, not required');
			continue;
		}
		$c = new Command($cmd);
		$c->addArgOption($check);
		$c->addArgOption('2>&1');
		$retval = $c->exec();
		$output = $c->getOutput();
		if ($retval==0 || ($cmd=='curl' && $retval==2)) {
			sayOK( $output[0] );
		} else {
			sayFailed( $output[0]. " (got exit code $retval)" );
		}
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
			sayFailed( "Failed ".$result );
		line_end($descr);
	}
}

function resources() {
	line_start('Application temp folder: ');
	$tmp = System::getApplicationTemp();
	if (!is_dir($tmp)) {
		sayFailed($tmp . ' is not a folder');
	} elseif (!is_writable($tmp)) {
		sayFailed($tmp . ' is not writable');
	} else {
		sayOK($tmp . ' exists and is writable');
	}
	line_end();
	
	line_start('Script wrapper: ');
	$w = _command_getScriptWrapper();
	if (!$w) {
		sayOK('Not needed on this server');
	} elseif (!file_exists($w)) {
		sayFailed($w.' does not exist');
	} elseif (!is_executable($w)) {
		sayFailed($w.' is not executable');
	} else {
		sayOK($w);
	}
	line_end();
}

function localeSettings() {
	if(System::isWindows()) {
		$c = new Command('mode', false);
		$c->addArgOption('con', 'codepage', false);
		$c->exec();
		line_start('Windows console');
		$supported = array(850, 1252);
		$pattern = '/\s*(.*):\s*(\d+)\s*/';
		foreach ($c->getOutput() as $line) {
			if (preg_match($pattern, $line, $matches)) {
				line_start($matches[1]);
				$codepage = $matches[2];
				if (in_array($codepage, $supported)) {
					sayOK($codepage);
				} else {
					sayFailed($codepage);
				}
			}
		}
		return;
	}
	$c = new Command('locale', false);
	$c->exec();
	$locales = Array();
	foreach ($c->getOutput() as $locale) {
		list($env, $val) = explode('=', $locale);
		line_start($env);
		if (strpos($val, "UTF-8")===false) {
			sayFailed("$val Not UTF-8, LC_ALL or all other LC and LANG must be UTF-8.");
		} else {
			sayOK($val);
		}
		line_end();
	}
}

function serverVariables() {
	global $serverVariables;
	foreach ( $serverVariables as $command => $expect ) {
		line_start($command);
		if (!isset($_SERVER[$command])) {
			saySomething('Not set');
		} else {
			$result = $_SERVER[$command];
			if ($result == $expect)
				sayOK( $result );
			else
				saySomething( $result );
		}
		line_end();
	}
}

?>
