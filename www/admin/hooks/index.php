<?php
/**
 * Administration and execution of hook scripts.
 *
 * @package admin
 */
require('../../conf/repos.properties.php');
require('../../conf/System.class.php');
require('../../conf/Report.class.php');
require('../../conf/Command.class.php');

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
} else {
	showInfo();
}

// ---- the supported hook scripts ----
// Note that the output will be passed to the subversion client.

function runHook_post_commit($rev, $path) {
	// validation
	if (!is_numeric($rev)) trigger_error('Hooks require a numeric revision number.', E_USER_ERROR);
	if (!isAbsolute($path)) trigger_error('Repository path must be absolute.', E_USER_ERROR);
	
	// do the exports specified by config file
	if (!getConfig('exports_file')) trigger_error('No exports defined. Set "exports_file" in repos.properties.');
	$exports_file = getConfig('admin_folder').getConfig('exports_file');
	
	$exports = parse_ini_file($exports_file);
	$approvedEports = array();
	foreach ($exports as $src => $target) {
		if (isAbsolute($target)) {
			trigger_error('For security reasons, export targets must start with recognized keywords.', E_USER_ERROR);
		} else {
			$p = strpos($target, '/');
			$folder = substr($target, 0, $p);
			$target = getRealPath($folder) . substr($target, $p+1);
			$approvedEports[$src] = $target;
		}
	}
	
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
		$entry = trim($matches[3]);
		if (isset($approvedEports[$entry]) && ($matches[1] == 'U' || $matches[1] == 'A')) {
			_exportFile($path, '/'.$entry, $rev, $approvedEports[$entry]);	
		} else {
			// do nothing
		}
	}
	
}

function _exportFile($repo, $path, $revision, $destination) {
	$c = new Command('svnlook');
	$c->addArgOption('cat');
	$c->addArgOption('-r '.$revision);
	$c->addArg($repo);
	$c->addArg($path);
	$c->exec();
	$out = $c->getOutput();
	if ($c->getExitcode()) {
		trigger_error("Could not read committed file $path revision $revision: ".implode("\n",$out), E_USER_ERROR);
	} else {
		$handle = fopen($destination, 'w');
		for ($i = 0; $i < count($out); $i++) {
			fwrite($handle, $out[$i].System::getNewline());
		}
		fclose($handle);
		echo ("Exported $path to $destination\n");
	}
}

/**
 * Derive the 
 *
 * @param String $hostFolder keyword folder without trailing slash
 * @return String absolute local path if keyword is recognized
 */
function getRealPath($hostFolder) {
	$known_folders = array(
		'admin' => getConfig('admin_folder'),
		'html' => toPath(dirname(dirname(dirname(dirname(__FILE__)))).'/'),
		'backup' => getConfig('backup_folder'),
		'repo' => getConfig('local_path')
	);
	if (!array_key_exists($hostFolder, $known_folders)) {
		trigger_error("Export target '$target' is not recognized.", E_USER_ERROR);
		exit; // for the sake of security
	}
	return $known_folders[$hostFolder];
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
				// maybe show execution log or something
			} else {
				$r->info('To let Repos create a hook script, delete the existing file.');
			}
		}
	}
	$r->display();
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
	$r->info('<a href="./">Return to hooks administration</a>');
	$r->display();
}

?>
