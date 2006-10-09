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
 */

require(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
$report = new Report('set up test repository');

// name the temp dir where the repository will be. This dir will be removed recursively.
$test_repository_folder="test.repos.se";

// can not use the repos temp dir because it can be deleted anytime
//$test = getTempDir($test_repository_folder);
$test = getSystemTempDir().$test_repository_folder.'/';

// the apache config file to include from the subversion host
$conffile = $test . "admin/testrepo.conf";

# environment setup, should be valid for both 'svn' and 'svnadmin'
$here=dirname(__FILE__);
$svnargs="--config-dir " . rtrim($here, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "test-svn-config-dir";

// one call to escapeArgument needed as long as the excape check is enabled in repos.properties
escapeArgument("info");

$report->info("To use this repository, do \"Include $conffile\" from a conf-file and restart Apache.");

function setup_svnadmin($command) {
	global $svnargs, $report;
	$cmd = $svnargs.' '.$command;
	$result = repos_runCommand('svnadmin', $cmd);
	if (array_pop($result)) {
		$report->fail("svnadmin command failed: $command");
	} else {
		$report->ok("Successfully executed svnadmin command: $command");
		$report->debug($result);
	}	
}

function setup_svn($command) {
	global $svnargs, $report;
	$cmd = $svnargs.' '.$command;
	$result = repos_runCommand('svn', $cmd);
	if (array_pop($result)) {
		$report->fail("Svn command failed: $command");
	} elseif (count($result)==0) {
		$report->fail("Svn command returned no result: $command");
	} else {
		$report->ok("Successfully executed svn command: $command");
		$report->debug($result);
	}	
}



?>