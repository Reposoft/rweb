<?php
require( dirname(dirname(__FILE__)) . '/conf/Report.class.php' );
require( dirname(__FILE__) . '/admin.inc.php' );

//$report = new Report('System status');

$repourl = getRepository();
$repodir = getConfig('local_path');
if ( !isRepository($repodir) )
	fatal("repository '$repourl' is not available locally");
$headrev = getHeadRevisionNumber($repodir);
$backupdir = getConfig( 'backup_folder' );
$backupprefix = getPrefix( $repodir );
$backup = getCurrentBackup($backupdir, $backupprefix);

/**
 * echo backup file status as HTML, start, gaps and end
 * @return last revision number of backup, -1 if no backup found
 */
function getBackupInfoAsHtml($backupArray) {
	if (count($backupArray)<1) {
		echo "<p>No backup files found</p>";
		return;
	}
	echo "<p>";
	echo count($backupArray);
	echo " backup files";
	echo ", from revision <span class=\"revision\">";
	echo $backupArray[0][1];
	echo "</span> to <span class=\"revision\">";
	echo $backupArray[count($backupArray) - 1][2];
	echo "</span>";
	echo "</p>";
	// look for gaps
	$lastrev = -1;
	$isgaps = false;
	foreach ($backupArray as $file) {
		if ( $file[1] != $lastrev + 1 ) {
			fail("Backup gap. Revision " . ($lastrev + 1) . " to " . ($file[1] - 1) . " missing. ");
			if (!$isgaps) {
				$isgaps = true;
				warn("The best solution to this is currently to delete all backup files after revision ". ($file[1]-1) ." (and the corresponding entries in the MD5SUMS file) and then <a href=\"backup/\">run the backup script.</a>");	
			}
		}
		$lastrev = $file[2];
	}
	return $lastrev;
}

?>
<p><a href="../conf/index.php">Check configuration</a></p>
<p><a href="configure/">Propose system configuration</a></p>
<h2>Backup status</h2>
<table id="repository_list" class="rows">
<tr>
<th><?php echo $repourl; ?></th>
<td>At revision <span class="revision"><?php echo $headrev; ?></span></td>
</tr><tr>
<td colspan="2"><?php $lastrev = getBackupInfoAsHtml($backup); ?>
</td>
</tr>
<?php if ($headrev > $lastrev) { // allow incremental backup ?>
<tr>
<td colspan="2">
<p>The repository has revisions up to <span class="revision"><?php echo($headrev); ?></span> 
that are newer than the latest backup <span class="revision"><?php echo($lastrev); ?></span></p>
<p>Regular automated backups is preferred, but you can also <a href="backup/">run incremental backup manually</a>.</p>
</td>
<?php } ?>
</table>
<p><a id="verify" href="verify/" class="action">Verify current backup files</a></p>
<?php html_end() ?>
