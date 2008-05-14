<?php
/**
 * Presents a brief overview of the system status.
 * 
 * @package admin
 */

require( 'repos-backup.inc.php' );
require( ReposWeb.'conf/Presentation.class.php' );

//$report = new Report('System status');

$repourl = getRepository();
$repodir = getBackupRepo();
if ( !isRepository($repodir) )
	trigger_error("Repository '$repourl' is not available locally. A path that points to a subversion repository folder should be configured.", E_USER_ERROR);
$headrev = getHeadRevisionNumber($repodir);
$backupdir = getBackupFolder();
$backupprefix = getPrefix( $repodir );
$backup = getCurrentBackup($backupdir, $backupprefix);

$p = new Presentation('Repos administration');
$p->assign('repository', $repourl);
$p->assign('headrev', $headrev);
$p->assign('backupfiles', count($backup));

if (count($backup)==0) {
	$p->assign('lastrev', 0);
	$p->display();
} else {
// this logic fails if there is no backup array	
$p->assign('lastrev', $backup[count($backup) - 1][2]);

$lastrev = -1;
$p->assign('hasoverlap', false);
$p->assign('hasgaps', false);
foreach ($backup as $file) {
	if ( $file[1] < $lastrev + 1 ) {
		$p->assign('hasoverlap', true);
		$p->append('overlaps', array('from'=>$file[1], 'to'=>$lastrev));
	}
	if ( $file[1] > $lastrev + 1 ) {
		$p->assign('hasgaps', true);
		$p->append('gaps', array('from'=>$lastrev + 1, 'to'=>$file[1] - 1));
	}
	$lastrev = $file[2];
}

$p->assign('backup', $backup);
$p->display();

}
?>
