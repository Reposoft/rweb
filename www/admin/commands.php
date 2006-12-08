<?php
/**
 * Administration from command line
 * 
 * @package admin
 */
 
require( dirname(__FILE__) . '/repos-backup.inc.php' );

define("EXIT_OK", 0);
define("EXIT_ABNORMAL", 1);
// default return code is 0, used by the exit call after the commands 
$ok = true;

// Command line mode
$args = count( $argv ) - 1;
if ( $args == 0 || eregi("-*help",$argv[1])>0 ) {
	echo "\n";
	echo "Repos.se administration, backup script version " . BACKUP_SCRIPT_VERSION . "\n";
	echo "\n";
	echo "Usage:\n";
	echo "php " . __FILE__ . " command [parameters]\n";
	echo "Supported commands are: dump, load, verify, verifyMD5\n";
	echo "To get syntax help, write only the command name.";
	echo "\n";
} elseif ( $argv[1] == "dump" ) {
	if ($args != 3 || eregi("-*help",$argv[2])>0 )
		echo "Usage: dump repository-path backup-path\nPaths should have no tailing slash\n";
	else
		dump($argv[2], $argv[3], getPrefix($argv[2]));
} elseif ( $argv[1] == "load" ) {
	if ($args < 3 || eregi("-*help",$argv[2])>0 )
		echo "Load backup files into existing repository.\nUsage: load repository-path backup-path [prefix]\nDefault prefix is derived from repository path.\nPaths should have no tailing slash\n";
	else
		load($argv[2], $argv[3], isset($argv[4]) ? $argv[4] : getPrefix($argv[2]));
} elseif ( $argv[1] == "verify" ) {
	if ($args != 2 || eregi("-*help",$argv[2])>0 )
		echo "Verify repository.\nUsage: verify repository-path\nPaths should have no tailing slash\n";
	else
		verify($argv[2]);
} elseif ( $argv[1] == "verifyMD5" ) {
	if ($args != 2 || eregi("-*help",$argv[2])>0 )
		echo "Verify that each entry in MD5SUMS file has a matching file.\nUsage: verifyMD5 backup-path\nPaths should have no tailing slash\n Exit code 0 <=> all checksums OK.";
	else
		$ok = verifyMD5($argv[2]);
} elseif ( $argv[1] == "htmlStart" ) {
	// admin feature. Print headers to show the command output as html file.
	html_start(date("Y-m-d H:i:s"));
}

if ($ok)
	exit (EXIT_OK);
else
	exit (EXIT_ABNORMAL);

?>