<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/edit.class.php" );

// automatic validation
new Rule('tofolder');
new FilenameRule('newname');
// explicit validation
new NewFilenameRule('newname', $_GET['tofolder']);

// dispatch
if (isset($_GET[SUBMIT])) {
	svnCopy(); 
} else {
	$target = getTarget();
	$template = new Presentation();
	$template->assign('repository', getRepository());
	$template->assign('target', $target);
	$template->assign('oldname', basename($target));
	$template->assign('folder', getParent($target));
	
	$template->display();
}

function svnCopy() {
	Validation::expect('target', 'tofolder', 'newname', 'move', 'message');
	if ($_GET['move']==1) {
		$edit = new Edit('move');
	} else {
		$edit = new Edit('copy');
	}
	$oldUrl = getTargetUrl();
	$tofolder = rtrim($_GET['tofolder'], '/').'/'; // don't require tailing slash from user
	$newUrl = getRepository().$tofolder.$_GET['newname'];
	if (isset($_GET['message'])) {
		$edit->setMessage($_GET['message']);
	}
	$edit->addArgUrl($oldUrl);
	$edit->addArgUrl($newUrl);
	$edit->execute();
	$edit->present(new Presentation());
}
?>