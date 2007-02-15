<?php
/**
 * Presents a brief overview of the system status.
 * 
 * @package admin
 */

require( dirname(dirname(__FILE__)) . '/conf/Presentation.class.php' );
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

$p = new Presentation('Repos administration');
$p->assign('repository', $repourl);
$p->assign('headrev', $headrev);
$p->assign('lastrev', $backup[count($backup) - 1][2]);

$lastrev = -1;
foreach ($backup as $file) {
	if ( $file[1] != $lastrev + 1 ) {
		$p->assign('hasgaps', true);
		$p->append('gaps', array('from'=>$lastrev + 1, 'to'=>$file[1] - 1));
	}
	$lastrev = $file[2];
}

$p->assign('backup', $backup);
$p->assign('backupfiles', count($backup));
$p->display();
