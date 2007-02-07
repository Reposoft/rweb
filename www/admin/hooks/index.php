<?php
/**
 * Administration and execution of hook scripts.
 *
 * @package admin
 */
require('../../conf/repos.properties.php');
require('../../conf/System.class.php');
require('../../conf/Report.class.php');
if (!class_exists('Command')) require('../../conf/Command.class.php');
require('export.inc.php');

$known_hooks = array(
	'post-commit'
);

if (isset($_GET['create'])) {
	$type = $_GET['create'];
	if (!in_array($type, $known_hooks)) trigger_error($type.' is not a supported hook', E_USER_ERROR);
	createHook($type);
} elseif (isset($_GET['run'])) {
	header('Content-Type: text/plain');
	$type = $_GET['run'];
	if (!in_array($type, $known_hooks)) trigger_error($type.' is not a supported hook', E_USER_ERROR);
	if (!isset($_GET['rev'])) trigger_error('Revision required for hook '.$type, E_USER_ERROR);
	//if (!isset($_GET['repo'])) trigger_error('Repository path required for hook '.$type, E_USER_ERROR);
	$repo = getConfig('local_path');
	$func = 'runHook_'.strtr($type,'-','_');
	if (!function_exists($func)) {
		trigger_error('Can not execute '.$type.' hook. No Repos function for it.', E_USER_ERROR);
	}
	call_user_func($func, $_GET['rev'], $repo);
} elseif (isset($_GET['test'])) {
	header('Content-Type: text/plain');
	$type = $_GET['test'];
	if (!in_array($type, $known_hooks)) trigger_error($type.' is not a supported hook', E_USER_ERROR);
	if (!isset($_GET['rev'])) trigger_error('Revision required for hook '.$type, E_USER_ERROR);
	//if (!isset($_GET['repo'])) trigger_error('Repository path required for hook '.$type, E_USER_ERROR);
	$repo = getConfig('local_path');
	testRun($type, $_GET['rev'], $repo);
} else {
	showInfo();
}

// ---- the supported hook scripts ----
// Note that the output will be passed to the subversion client.

function runHook_post_commit($rev, $repo) {
	if (!is_numeric($rev)) trigger_error('Hooks require a numeric revision number.', E_USER_ERROR);
	if (!isAbsolute($repo)) trigger_error('Repository path must be absolute.', E_USER_ERROR);
	
	$changes = hooksGetChanges($rev, $repo);
	if (getConfig('access_file')) exportAdministration($rev, $repo, $changes);
	if (getConfig('exports_file')) exportOptional($rev, $repo, $changes);
	// user must be exported last, if password is changed
	if (getConfig('users_file')) exportUsers($rev, $repo, $changes);
}

/**
 * Uses svnlook to list the changes in a revision.
 * @return array [path with no leading slash] => [ADUP] (where P=property change)
 */
function hooksGetChanges($rev, $path) {
	$changes = array();
	// get the changes of the revision
	$c = new Command('svnlook');
	$c->addArgOption('changed');
	$c->addArgOption('-r '.$rev);
	$c->addArg($path);
	$c->exec();
	// see if any of the updated files should be exported
	$pattern = '/^([ADU_])([U\s])\s+(.*)/';
	foreach ($c->getOutput() as $change) {
		preg_match($pattern, $change, $matches);
		if (!isset($matches[3])) trigger_error('There are no repository changes in revision '.$rev, E_USER_ERROR);
		$entry = trim($matches[3]);
		$change = $matches[1];
		if (strpos('ADU', $change)===false) $change = 'P'; // for property
		$changes[$entry] = $change;
	}
	return $changes;
}

function testRun($type, $rev, $repoPath) {
	if (!is_numeric($rev)) trigger_error('Rev must be numeric ', E_USER_ERROR);
	$script = getHookScriptPath($type);
	// run plain exec so we don't get a controlled environment
	echo("---- test execution of $type hook ----\n\n");
	$cmd = "$script \"$repoPath\" $rev"; // like subversion calls it
	passthru($cmd, $return);
	if ($return) {
		// the hook is either not installed or returned error
		echo("\n---- failed with exit code $return ----\n");
	} else {
		echo("\n---- no errors reported to caller ----\n");
	}
}

// ------------------------------------

function showInfo() {
	global $known_hooks;
	$r = new Report('Subversion hook scripts');
	$r->info('No operation selected.');
	foreach ($known_hooks as $hook) {
		$f = getHookScriptPath($hook);
		if (!file_exists($f)) {
			$r->info($hook . ' hook script does not exist.');
			$r->info('<a href="?create='.$hook.'">Create '.$hook.' hook</a>');
		} else {
			if (checkHookScript($f, $hook, $r)) {
				$r->info('<form action="./" method="get"><input type="hidden" name="test" value="'.$hook.'"/>'.
				'Test this hook with revision <input name="rev" type="text" size="4" value="1"/>'.
				'<input type="submit" value="execute"/></form>'.
				'<p>Note that if you test with an old revision, newer configuration will be overwritten.</p>');
			} else {
				$r->info('To let Repos create a hook script, delete the existing file.');
			}
		}
	}
	$r->display();
}

function showTestCommand($type) {
	
}

function getHookScriptPath($type) {
	$local = getConfig('local_path');
	$hook = $local.'hooks/'.$type;
	if (System::isWindows()) {
		$hook .= '.bat';
	}
	return $hook;
}

/**
 * Verifies that a hook script contains Repos command
 *
 * @param String $path
 * @param String $scriptType
 * @param Report $report
 * @return boolean true if everything is in order
 */
function checkHookScript($path, $scriptType, $report) {
	$cmd = getHookCommand($scriptType);
	$fh = fopen($path, 'r');
	while (!feof($fh)) {
		$buffer = fgets($fh);
		if (preg_match('/^'.preg_quote($cmd,'/').'?/', $buffer)) {
			$report->ok($scriptType . ' hook contains the Repos integration command.');
			fclose($fh);
			return true;
		}
	}
	fclose($fh);
	$report->fail($scriptType . ' hook for this repository does not contain the Repos integration command.');	
	return true;
}

function getHookCommand($scriptType, $revVariable=null, $repoVariable=null) {
	$curl = System::getCommand('curl');
	$curl = $curl .= ' -s';
	$url = getWebapp().'admin/hooks/?run='.$scriptType;
	if ($revVariable) $url .= '&rev='.$revVariable;
	if ($repoVariable) $url .= '&repo='.$repoVariable;
	// verify that repos is configured correctly
	if (!strBegins($url, 'http')) {
		trigger_error('Need an absolut webapp url for hook scripts. Can not use: '.$url, E_USER_ERROR);
	}
	return "$curl \"$url\"";
}

/**
 * Creates a hook script with a command that integrates with this Repos script
 * The command uses 'curl' command line tool.
 * @param String $type
 */
function createHook($type) {
	$r = new Report('Create repository hook: '.$type);
	$f = getHookScriptPath($type);
	if (file_exists($f)) {
		$r->error($type.' hook already exists');
	}

	$hook = '';
	if (System::isWindows()) {
		$hook .= "@echo off\r\n";
		$hook .= "rem Integration with Repos\r\n";
		$hook .= 'set REV=%2'."\r\n";
		//$hook .= 'set REPO=%1'."\r\n";
		$hook .= getHookCommand($type, '%REV%')."\r\n";
	} else {
		$hook .= "#!/bin/sh\n";
		$hook .= "# Integration with Repos\n";
		$hook .= 'REV="$2"'."\n";
		//$hook .= 'REPO="$1"'."\n";
		// using non-blocking call to save on concurrent apache threads
		$hook .= getHookCommand($type, '$REV')." &\n";
	}
	
	if (!is_writable(dirname($f))) {
		$r->error('No write access to repository hook script folder.');
		$r->info('<pre>'.$hook.'</pre>');
	}

	// write
	System::createFileWithContents($f, $hook);
	$r->ok($type . ' hook script created.');
	if (chmod($f, 0774)) {
		$r->ok('Gave execution permission to user and group, readonly for others');
	} else {
		$r->warn('Could not set execution permissions on file. Please check that it is executable by web server.');
	}
	$r->info('<a href="./">Return to hooks administration</a>');
	$r->display();
}

?>
