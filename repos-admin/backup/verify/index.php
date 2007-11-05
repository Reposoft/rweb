<?php
/**
 * Web based access to the backup verification routines.
 * Call this script's url from a scheduler to get regular verification.
 * 
 * @package admin
 */

require('../repos-backup.inc.php' );

require( ReposWeb.'/conf/Report.class.php' );
$report = new Report();

$backupFolder = getConfig('backup_folder');

verifyMD5($backupFolder);

$report->info('<p><a id="back" href="../" class="action">return to admin page</a></p>');

$report->display();

?>