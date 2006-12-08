<?php
/**
 * This is a work in progress. not usable yet.
 * 
 * --- Incremental repository export ---
 * Designed to run after every commit, for exmaple from post-commit hook.
 * Does a minimum export by analyzing verbose log of HEAD revision.
 * Also deletes files according to the last commit.
 * For full export specify 'full' on the command line or query parameter full=1
 * 
 * @package admin
 */

require( dirname(__FILE__) . '/repos-admin.inc.php' );

$repourl = getConfig( 'repo_url' );
$exportfile = getConfig( 'admin_folder' ) . getConfig( 'export_file' );
$svncmd = getCommand( 'svn' ); 

html_start( "Incremental export: $repourl" );
debug( "Export paths file: $exportfile" );

$export = parse_ini_file( $exportfile, false );
if ( isset($_GET['full']) || ( isset($argv[1]) && $argv[1] == 'full' ) ) {
	foreach ( $export as $local => $url )
		export_full( $local, $url );
} else {
	foreach ( $export as $local => $url )
		export_incremental( $export, $url );
}

html_end();

# --- functions ---

function export_full( $local, $url ) {
	info( "Doing full export with forced overwrite to $local from repository path $url" );
}

function export_incremental() {
	info( "Doing incremental export to $local from repository path $url" );
	// how do we get which files have local modifications?
	//  check their last change i repo?
	//  check date of all files at every commit
}

function getExportDefinitions($frominifile) {

}

function getLocalModificationTime($filepath) {

}

/**
 * Figure out when a file was last changed in repository (before this commit)
 */
function getPreviousCommitTime($filehref) {

}

/**
 * Get repository log
 * @return array ('/repository/href' => SVN operation)
 */
function getParsedLog($href) {

}

?>