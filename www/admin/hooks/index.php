<?php
/**
 * Administration and execution of hook scripts.
 *
 * @package admin
 */
require('../../conf/repos.properties.php');
require('../../conf/System.class.php');
require('../../conf/Report.class.php');

$known_hooks = array(
	'post-commit'
);

if (isset($_GET['create'])) {
	$type = $_GET['create'];
	if (!in_array($type, $known_hooks)) trigger_error($type.' is not a supported hook', E_USER_ERROR);
	createHook($type);
} elseif (isset($_GET['run'])) {
	$type = $_GET['run'];
	if (!in_array($type, $known_hooks)) trigger_error($type.' is not a supported hook', E_USER_ERROR);
	$func = 'runHook_'.strtr($type,'-','_');
	if (!function_exists($func)) {
		trigger_error('Can not execute '.$type.' hook. No Repos function for it.', E_USER_ERROR);
	}
	
	call_user_func($func);
} else {
	showInfo();
}

// ---- the supported hook scripts ----
// Note that the output will be passed to the subversion client.

function runHook_post_commit() { //$committedRevision) {
	echo "(post commot hook invoked)";
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
	$match = '/^'.str_replace('?','\?',str_replace('/', '\/', $cmd)).'/';
	$fh = fopen($path, 'r');
	while (!feof($fh)) {
		$buffer = fgets($fh);
		if (preg_match($match, $buffer)) {
			$report->ok($scriptType . ' hook contains the Repos integration command.');
			fclose($fh);
			return true;
		}
	}
	fclose($fh);
	$report->fail($scriptType . ' hook for this repository does not contain the Repos integration command.');	
	return true;
}

function getHookCommand($scriptType) {
	$curl = System::getCommand('curl');
	$curl = $curl .= ' -s';
	$url = getWebapp().'admin/hooks/?run='.$scriptType;
	// verify that repos is configured correctly
	if (!strBegins($url, 'http')) {
		trigger_error('Need an absolut webapp url for hook scripts. Can not use: '.$url, E_USER_ERROR);
	}
	return "$curl $url";
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

	$hook = array(
		System::isWindows() ? '@echo off' : '#!/bin/sh',
		(System::isWindows() ? 'rem' : '#').' integration with Repos',
		getHookCommand($type)
	);
	//REPO="$1"
   //REV="$2"
	
	if (!is_writable(dirname($f))) {
		$r->debug($hook);
		$r->error('No write access to repository hook script folder.');
	}

	// write
	$fh = fopen($f, 'w');
	foreach($hook as $line) {
		fwrite($fh, $line);
		if (System::isWindows()) {
			fwrite($fh, "\r\n");
		} else {
			fwrite($fh, "\n");
		}
	}
	fclose($fh);
	
	$r->ok($type . ' hook script created.');
	$r->info('<a href="./">Return to hooks administration</a>');
	$r->display();
}

?>
