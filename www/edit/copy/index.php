<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/SvnEdit.class.php" );
addPlugin('validation');

// automatic validation
new Rule('tofolder');
new FilenameRule('newname');
// explicit validation of the destination
$tofolder = rtrim($_GET['tofolder'], '/').'/';// don't require tailing slash from user;
new NewFilenameRule('newname', $tofolder);

// dispatch
if (isset($_GET[SUBMIT])) {
	svnCopy($tofolder); 
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$file = new SvnOpenFile($target);
	$template->assign_by_ref('file', $file);
	$template->assign('repository', getRepository());
	$template->assign('target', $target);
	$template->assign('oldname', getPathName($target));
	$template->assign('folder', getParent($target));
	
	$template->display();
}

function svnCopy($tofolder) {
	Validation::expect('target', 'tofolder', 'newname', 'move', 'message');
	if ($_GET['move']==1) {
		$edit = new SvnEdit('move');
	} else {
		$edit = new SvnEdit('copy');
	}
	$oldUrl = getTargetUrl();
	$newUrl = getRepository().$tofolder.$_GET['newname'];
	if (isset($_GET['message'])) {
		$edit->setMessage($_GET['message']);
	}
	$edit->addArgUrl($oldUrl);
	$edit->addArgUrl($newUrl);
	$edit->exec();
	displayEdit(Presentation::getInstance());
}
?>