<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/edit.class.php" );

// automatic validation
new FilenameRule('oldname');
new FilenameRule('newname');
// explicit validation
new NewFilenameRule('newname', getTarget());

// dispatch
if (isset($_GET[SUBMIT])) {
	svnRename(); 
} else {
	$target = getTarget();
	$template = new Presentation();
	$template->assign('target', getParent($target));
	$template->assign('oldname', basename($target));
	$template->assign('repository', getRepository().getParent($target));
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function svnRename() {
	Validation::expect('target', 'oldname', 'newname', 'message');
	$edit = new Edit('move');
	$oldUrl = getTargetUrl().$_GET['oldname'];
	$newUrl = getTargetUrl().$_GET['newname'];
	if (isset($_GET['message'])) {
		$edit->setMessage($_GET['message']);
	}
	$edit->addArgUrl($oldUrl);
	$edit->addArgUrl($newUrl);
	$edit->execute();
	$edit->present(new Presentation(), getTargetUrl());
}
?>