<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/SvnEdit.class.php" );

targetLogin(); // edit operation can not be public

// automatic validation
new FilenameRule('newname');
// svn import: parent folder must exists, to avoid implicit create
$parent = new ResourceExistsRule('tofolder');
// explicit validation of the destination
$tofolder = rtrim($parent->getValue(), '/').'/';// don't require tailing slash from user;
new NewFilenameRule('newname', $tofolder);
$revisionRule = new RevisionRule();

// dispatch
if ($_SERVER['REQUEST_METHOD']=='POST') {
	svnCopy($tofolder); 
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$file = new SvnOpenFile($target, $revisionRule->getValue());
	$file->isWritable(); // check before page is displayed because it might require authentication
	$template->assign_by_ref('file', $file);
	$template->assign('repository', getRepository());
	$template->assign('target', $target);
	$template->assign('oldname', getPathName($target));
	$template->assign('folder', getParent($target));
	
	$template->display();
}

function svnCopy($tofolder) {
	Validation::expect('target', 'tofolder', 'newname', 'move', 'message');
	$template = Presentation::background();
	if ($_POST['move']==1) {
		$edit = new SvnEdit('move');
	} else {
		$edit = new SvnEdit('copy');
	}
	$oldUrl = getTargetUrl();
	$newUrl = getRepository().$tofolder.$_POST['newname'];
	if (isset($_POST['message'])) {
		$edit->setMessage($_POST['message']);
	}
	if (isset($_POST['rev'])) {
		$edit->addArgUrlPeg($oldUrl, $_POST['rev']);	
	} else {
		$edit->addArgUrl($oldUrl);
	}
	$edit->addArgUrl($newUrl);
	$edit->exec();
	displayEdit($template);
}
?>
