<?php

/**
 * Administration from command line
 */
 
require( dirname(__FILE__) . '/repos-backup.inc.php' );

// Command line mode
$args = count( $argv ) - 1;
if ( $args == 0 || eregi("-*help",$argv[1])>0 ) {
	echo "Repos.se administration, backup script version " . BACKUP_SCRIPT_VERSION . "\n";
	echo "Usage: php " . __FILE__ . " command [parameters]\n";
	echo "Supported commands are: dump, load, verify, verifyMD5\n";
} elseif ( $argv[1] == "dump" ) {
	if ($args != 3 || eregi("-*help",$argv[2])>0 )
		echo "Usage: dump repository-path backup-path\n";
	else
		dump($argv[2], $argv[3], getPrefix($argv[2]));
} elseif ( $argv[1] == "load" ) {
	if ($args < 3 || eregi("-*help",$argv[2])>0 )
		echo "Load backup files into existing repository.\nUsage: load repository-path backup-path [prefix]\nDefault prefix is derived from repository path.\n";
	else
		load($argv[2], $argv[3], isset($argv[4]) ? $argv[4] : getPrefix($argv[2]));
} elseif ( $argv[1] == "verify" ) {
	if ($args != 2 || eregi("-*help",$argv[2])>0 )
		echo "Verify repository.\nUsage: verify repository-path\n";
	else
		verify($argv[2]);
} elseif ( $argv[1] == "verifyMD5" ) {
	if ($args != 2 || eregi("-*help",$argv[2])>0 )
		echo "Verify that each entry in MD5SUMS file has a matching file.\nUsage: verifyMD5 backup-path\n";
	else
		verifyMD5($argv[2]);
}

?>