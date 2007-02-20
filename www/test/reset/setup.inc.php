<?php
/**
 * Common code in setup scripts.
 * 
 * Share variables:
 * $report - Report to write script results to.
 * $test - the folder that should contain the respository.
 * $conffile - the apache conff file, to have same "Include" for any test repository. 
 * 
 * Allows the commands "svn" and "svnadmin", using the path resolution from repos.properties.php
 * On windows, the PATH to SVN and SVNADMIN has to be defined in the SYSTEMPATH, not in the USERPATH
 * 
 * @package test
 */
require(dirname(dirname(dirname(__FILE__))).'/conf/Command.class.php');
require(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
require(dirname(dirname(dirname(__FILE__))).'/open/ServiceRequest.class.php');
$report = new Report('set up test repository');

// name the temp dir where the repository will be. This dir will be removed recursively.
//$test_repository_folder="test.repos.se";

$allow = getConfig('allow_reset');
if ($allow != 1) $report->fatal('Not allowed to reset this repository. Set allow_reset = 1 in config file');

// --- valriables used by all reset scripts ---
$repo = getConfig('local_path');
$admin = getConfig('admin_folder');
$backup = getConfig('backup_folder');

$userfile = $admin . getConfig('users_file');
$aclfile = $admin . getConfig('access_file');
// --------------------------------------------

// the apache config file to include from the subversion host
// generated config does not contain a VirtualHost directive,
//  so the file must be included from within a virtual host (or the default host)
$conffile = $admin . "testrepo.conf";
// the repository root on the webserver
$conflocation = '/testrepo';

// environment setup, should be valid for both 'svn' and 'svnadmin'
$here=dirname(__FILE__);
$svnargs="--config-dir " . rtrim($here, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "test-svn-config-dir";

$report->info("To use this repository, do \"Include $conffile\" from a conf-file and restart Apache.");

// delete current repository and create empty $repo folder
// delete $userfile and $aclfile from admin folder
// create backup folder if it does not exist, but don't delete beckup if it exists
function setup_deleteCurrent() {
	global $repo, $report, $admin, $userfile, $aclfile, $backup;
	
	if (file_exists($repo)) {
		$report->info("Deleting old test repository folder $repo");
		// repositories usually have write protected contents
		if (file_exists($repo.'format')) chmod($repo.'format', 0755);
		if (file_exists($repo.'db/format')) chmod($repo.'db/format', 0755);
		System::deleteFolder($repo);
	}
	
	$report->info("Create empty repository folder at $repo");
	System::createFolder($repo);
	
	if (!is_dir($backup)) System::createFolder($backup); else $report->info("Keeping backup in $backup");
	
	if (!file_exists($admin)) {
		$report->info("Creating empty admin folder $admin");
		System::createFolder($admin);
	} else {
		$report->info("Using the existing admin folder $admin");
	}
}

function setup_getTempWorkingCopy() {
	$wc = System::getTempFolder('test-wc');

	if (file_exists($wc)) System::deleteFolder($wc);
	System::createFolder($wc);
	return $wc;
}

function setup_createHooks() {
	global $report;
	$url = 'admin/hooks/';
	$params = array('create' => 'post-commit');
	$s = new ServiceRequest($url, $params, false);
	$s->setResponseType(SERVICE_TYPE_TEXT);
	$s->exec();
	$report->debug($s->getResponse());
	if ($s->isOK()) {
		$report->ok('Created default hook scripts for this repository');
	} else {
		$report->fail('Could not create hook scripts. Got status '.$s->getStatus());
	}
}

function setup_exportUsers() {
	// export users from repository
}

function setup_createTestUsers() {
	global $userfile, $report;

	$users = 
	// demo user svensson:medel
	'svensson:$apr1$h03.....$vSQzcy3gId0sKgc/JvRCs.:Testuser Svensson:test@repos.se'."\n".
	// test:test
	'test:$apr1$Sy2.....$zF88UPXW6Q0dG3BRHOQ2m0:Testuser Test:test@repos.se'."\n".
	// tricky username, password 'medel' (but still only with Latin1 characters)
	'Sv@n s-on:$apr1$Q14.....$mwLZfXdpQ56dJ00TMEZPu/:Sv@n s-on:test@repos.se'."\n".
	// admin:admin, no name or email
	'admin:$apr1$JW3.....$r0aF2nCj00/Q6I8438Xsm1'."\n";
	
	if (System::createFileWithContents($userfile, $users, true, true)) {
		$report->ok("Successfully created user account file $userfile with MD5 encoded passwords");
	} else {
		$report->fail("Could not create user account file $userfile");
	}
}

function setup_createApacheLocation($extraDirectives='', $extraAfterLocation='') {
	global $conffile, $report, $repo, $userfile, $conflocation;
	$report->info("create apache 2.2 config");
	$conf = "
	<Location $conflocation>
	DAV svn
	SVNIndexXSLT \"/repos/view/repos.xsl\"
	SVNPath {$repo}
	SVNAutoversioning on
	# user accounts from password file
	AuthName \"$conflocation\"
	AuthType Basic
	AuthUserFile $userfile
	Require valid-user
	$extraDirectives
	</Location>
	$extraAfterLocation
	";
	if (System::createFileWithContents($conffile, $conf, true, true)) {
		$report->ok("Successfully created apache config file $conffile");
	} else {
		$report->fail("Could not create apache config file $conffile");
	}
}

function setup_replaceInFile($absolutePath, $replacements) {
	$f = fopen($absolutePath, 'r');
	$contents = fread($f, 32768);
	fclose($f);
	foreach($replacements as $find => $replace) {
		$contents = str_replace($find, $replace, $contents);
	}
	$f = fopen($absolutePath, 'w');
	fwrite($f, $contents);
	fclose($f);
}

// try to restart apache in one command (that will be slightly delayed so that this page completes
// call this at the end of setup script
function setup_reloadApacheIfPossible() {
	global $report, $conflocation;
	$report->info("Trying to restart Apache2 service");
	if (System::isWindows()) {
		$check = new Command('sc', false);
		$check->addArgOption('query');
		$check->addArgOption('Apache2');
		if ($check->exec()) {
			$report->debug($check->output);
			$report->warn('Apache2 service not found. Please restart Apache manually.');
			return;
		}
		$report->debug($check->output);
		
		$script = '@echo off'."\n".
		'ping 1.1.1.1 -n 1 -w 1000 >NUL'."\n". // wait for the reset script to terminate
		'sc stop Apache2 >NUL'."\n".
		':wait'."\n".
		'ping 1.1.1.1 -n 1 -w 1000 >NUL'."\n".
		'sc query Apache2 > %TEMP%\apache2_state.txt'."\n".
		'find "STOPPED" %TEMP%\apache2_state.txt'."\n".
		'if ERRORLEVEL 1 goto wait'."\n".
		'sc start Apache2 >NUL'."\n";
		
		$bat = System::getApplicationTemp('reset').'restartapache.bat';
		System::createFileWithContents($bat, $script, true, true);
		
		// it is not easy to start a process in the background on windows
		$WshShell = new COM("WScript.Shell");
		$oExec = $WshShell->Run("cmd /C \"$bat\" /S %windir%", 0, false);

		$report->ok("Apache will restart in a few seconds.");
	}
}

function setup_svnadmin($command) {
	global $svnargs, $report;
	$cmd = new Command('svnadmin');
	$cmd->addArgOption($svnargs);
	$cmd->addArgOption($command);
	if ($cmd->exec()) {
		$report->debug($cmd->getOutput());
		$report->fail("svnadmin command failed: $command");
	} else {
		$report->ok("Successfully executed svnadmin command: $command");
		$report->debug($cmd->getOutput());
	}
}

function setup_svn($command) {
	global $svnargs, $report;
	$command = setup_customizeCommand($command);
	$cmd = new Command('svn');
	$cmd->addArgOption($svnargs);
	$cmd->addArgOption($command);
	if ($cmd->exec()) {
		$report->fail("Svn command failed: $command");
	} elseif (count($cmd->getOutput())==0) {
		$report->fail("Svn command returned no result: $command");
	} else {
		$report->ok("Successfully executed svn command: $command");
		$report->debug($cmd->getOutput());
	}	
}

function setup_customizeCommand($command) {
	// preserve locks set during setup
	return str_replace('commit ', 'commit --no-unlock ', $command);
}

?>