<?php
require( dirname(__FILE__) . '/repos-admin.inc.php' );

html_start("status");

$repourl = getConfig( 'repo_url' );
$repodir = getConfig( 'local_path' );
if ( !isRepository($repodir) )
	fatal("repository '$repourl' is not available locally");
$headrev = getHeadRevisionNumber($repodir);
$backupdir = getConfig( 'backup_folder' );
$backupprefix = getPrefix( $repodir );
$backup = getCurrentBackup($backupdir, $backupprefix);

/**
 * @return backup file status as HTML, start, gaps and end
 */
function getBackupInfoAsHtml($backupArray) {
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
	foreach ($backupArray as $file) {
		if ( $file[1] != $lastrev + 1 )
			warn("Backup gap. Revision " . ($lastrev + 1) . " to " . ($file[1] - 1) . " missing. ");
		$lastrev = $file[2];
	}
}

?>
<h2>Repos configuration</h2>
<p><a href="../conf/index.php">Check configuration</a></p>
<p><a href="configuration.php">Propose system configuration</a></p>
<h2>Administration</h2>
<table id="repository_list">
<tr>
<th><?php echo $repourl; ?></th>
<td>At revision <span class="revision"><?php echo $headrev; ?></span></td>
</tr><tr>
<td colspan="2"><?php getBackupInfoAsHtml($backup); ?></td>
</tr>
</table>
<?php html_end() ?>
