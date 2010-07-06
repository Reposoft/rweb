<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/SvnEdit.class.php" );
addPlugin('validation');
addPlugin('filename');

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
	if (isset($_POST['s'])) {
		svnCopyMulti($tofolder, $_POST['s'], $revisionRule->getValue());
	} else {
		svnCopy($tofolder);
	}
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$file = new SvnOpenFileMulti($target, $revisionRule->getValue());
	$file->enableMultiIfFolder();
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

function svnCopyMulti($tofolder, $sourcePaths, $revision=false) {
	Validation::expect('message');
	$template = Presentation::background();
	$edit = new SvnEdit('copy');
	if (isset($_POST['message'])) {
		$edit->setMessage($_POST['message']);
	}
	// TODO verify encoding
	foreach ($sourcePaths as $s) {
		$surl = getTargetUrl($s);
		if (isset($_POST['rev'])) {
			$edit->addArgUrlPeg($surl, $_POST['rev']);	
		} else {
			$edit->addArgUrl($surl);
		}
	}
	$toUrl = getTargetUrl($tofolder);
	$edit->addArgUrl($toUrl);
	$edit->exec();
	displayEdit($template);
}
?>