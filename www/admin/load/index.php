<?php
/**
 * Loads backup files into repository.
 *
 * @package admin
 */
require('../repos-backup.inc.php');

require(dirname(dirname(dirname(__FILE__))) . "/conf/Report.class.php" );
$report = new Report();

$repodir = getConfig('local_path');
if ( !is_dir($repodir)) {
	fatal("The configured repository folder '$repodir' does not exist.");
}
if ( !isRepository($repodir) ) {
	$contents = getDirContents($repodir);
	if (count($contents) > 0) fatal("Folder '$repodir' is not empty and not a repository."); 
	fatal("Repository folder '$repodir' is enpty. Do <a href=\"../create/\">create repository</a>.");
}

$headrev = getHeadRevisionNumber($repodir);
debug("Repository '$repodir' contains revisions up to $headrev");

$backupdir = getConfig( 'backup_folder' );
$backupprefix = getPrefix( $repodir );
$backup = getCurrentBackup($backupdir, $backupprefix);
$files = count($backup);
if (!$files) fatal("There are no backup files in '$backupdir' matching $backupprefix*");
debug("Backup folder '$backupdir' contains $files backup files $backupprefix*");

$fromrev = $backup[$files-1][2] + 1;
if ( $fromrev - 1 == $headrev ) {
	info("No loading of backup needed, both repository and backup is at revision $headrev");
} else {
	// load directly without confirmation
	load($repodir, $backupdir, $backupprefix);
}

html_end();

?>
