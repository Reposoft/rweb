<?php
/**
 * Web based access to the backup verification routines.
 * Call this script's url from a scheduler to get regular verification.
 * 
 * @package admin
 */

require( dirname(dirname(__FILE__)).'/repos-backup.inc.php' );

require( ReposWeb.'/conf/Report.class.php' );
$report = new Report();

$backupFolder = getBackupFolder();

verifyMD5($backupFolder);

$report->info('<p><a id="back" href="../" class="action">return to admin page</a></p>');

$report->display();

?>