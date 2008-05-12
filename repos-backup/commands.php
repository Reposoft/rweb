<?php
/**
 * Administration from command line.
 * All paths arguments use native directory separators and may or may not end with one.
 * 
 * @package admin
 */

require( dirname(__FILE__) . '/backup/repos-backup.inc.php' );

define("EXIT_OK", 0);
define("EXIT_ABNORMAL", 1);
// default return code is 0, used by the exit call after the commands 
$ok = true;

$args = count( $argv ) - 1;
if ( $args == 0 || eregi("-*help",$argv[1])>0 ) {
	$report->info("");
	$report->info("Repos.se administration, backup script version " . BACKUP_SCRIPT_VERSION);
	$report->info("");
	$report->info("Usage:");
	$report->info("php " . __FILE__ . " command [parameters]");
	$report->info("Supported commands are: dump, load, verify, verifyMD5");
	$report->info("To get syntax help, write only the command name.");
	$report->info("");
} elseif ( $argv[1] == "dump" ) {
	if ($args != 3 || eregi("-*help",$argv[2])>0 )
		$report->info("Usage: dump repository-path backup-path");
	else
		dump(readPath($argv[2]), readPath($argv[3]), getPrefix($argv[2]));
} elseif ( $argv[1] == "load" ) {
	if ($args < 3 || eregi("-*help",$argv[2])>0 )
		$report->info("Load backup files into existing repository.\nUsage: load repository-path backup-path [prefix]\nDefault prefix is derived from repository path.");
	else
		load(readPath($argv[2]), readPath($argv[3]), isset($argv[4]) ? $argv[4] : getPrefix($argv[2]));
} elseif ( $argv[1] == "verify" ) {
	if ($args != 2 || eregi("-*help",$argv[2])>0 )
		$report->info("Verify repository.\nUsage: verify repository-path");
	else
		verify(readPath($argv[2]));
} elseif ( $argv[1] == "verifyMD5" ) {
	if ($args != 2 || eregi("-*help",$argv[2])>0 )
		$report->info("Verify that each entry in MD5 sums file has a matching file.\nUsage: verifyMD5 backup-path\n Exit code 0 <=> all checksums OK.");
	else
		$ok = verifyMD5(readPath($argv[2]));
} elseif ( $argv[1] == "htmlStart" ) {
	// admin feature. Print headers to show the command output as html file.
	html_start(date("Y-m-d H:i:s"));
}

if ($ok)
	exit (EXIT_OK);
else
	exit (EXIT_ABNORMAL);
	
function readPath($argument) {
	return rtrim(rtrim($argument, '/'),"\\").DIRECTORY_SEPARATOR;
}

?>