<?php
/**
 * Web based access to backup operations.
 * 
 * @package admin
 */

require('../repos-backup.inc.php');

require(dirname(dirname(dirname(__FILE__))) . "/conf/Report.class.php" );
$report = new Report();

$report->info("Run backup script, creating gzip archives of all revisions up to the current one.");
$report->debug("Locking the repository until backup is complete."); // TODO do we?

//$repourl = getRepository();
$repodir = getConfig('local_path');
if ( !isRepository($repodir) )
	fatal("repository '$repodir' is not available locally");
$backupdir = getConfig( 'backup_folder' );
$backupprefix = getPrefix( $repodir );

dump($repodir, $backupdir, $backupprefix);
$report->debug("Backup operation completed.");

$report->info('<p><a id="back" href="../" class="action">return to admin page</a></p>');

$report->display();

?>
