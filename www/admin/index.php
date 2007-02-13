<?php
/**
 * Presents a brief overview of the system status.
 * 
 * @package admin
 */

require( dirname(dirname(__FILE__)) . '/conf/Report.class.php' );
require( dirname(__FILE__) . '/admin.inc.php' );

//$report = new Report('System status');

$repourl = getRepository();
$repodir = getConfig('local_path');
if ( !isRepository($repodir) )
	fatal("Repository '$repourl' is not available locally. If the folder is empty, try <a href=\"create/\">create</a>.");
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
	if (!$isgaps) {
		info("Every revision from 0 to $lastrev is backed up.");
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
<?php } else if ($headrev < $lastrev) { ?>
<p>There are more revisions in the backup than in the repository. You can manually <a id="load" href="load/">load new backup files into repository</a>.</p>
<?php } ?>
</table>
<p><a id="verify" href="verify/" class="action">Verify current backup files</a></p>
<h2>Other tools</h2>
<p><a id="lib" href="../lib/">3rd party libraries</a></p>
<p><a id="hooks" href="hooks/">Repository hook scripts</a></p>
<p><a id="size" href="size/">Storage space used for this Repos host</a></p>
<p><a id="client" href="client/">Status for internal svn client</a></p>
<p><a id="testemail" href="testemail/">Test outgoing application e-mails</a></p>
<p><a id="accountreset" href="accountreset/">Reset a user's password</a></p>
<p><a id="accountcreate" href="../create/">Create user account</a></p>
<p><a id="accountdelete" href="../delete/">Delete user account</a></p>
<!-- TODO move these to separate page and include with ajax box -->
<div class="section">
<h2>Server information</h2>
<p>Primary server: <?php echo($_SERVER['REPOS_PRIMARY'] ? 'yes' : 'no'); ?></p>
</div>
<div class="section">
<h2>Client information</h2>
<p>Address: <?php echo($_SERVER['REMOTE_ADDR']); ?></p>
<p>Repos local: <?php echo($_SERVER['IS_LOCAL_CLIENT'] ? 'yes' : 'no'); ?></p>
<p>Repos admin: <?php echo($_SERVER['IS_ADMIN_CLIENT'] ? 'yes' : 'no'); ?></p>
</div>
<div class="section">
<h2>Repository administration</h2>
<!-- as ajax box? -->
<p><a href="<?php echo($repourl.'/administration/'); ?>" target="_blank">Log in to administration area</a></p>
</div>
<?php html_end() ?>
