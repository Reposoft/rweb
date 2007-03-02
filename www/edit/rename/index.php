<?php
require("../../conf/Presentation.class.php" );
require("../SvnEdit.class.php" );
addPlugin('validation');

// automatic validation
new Rule('folder');
new FilenameRule('newname');
// explicit validation
new NewFilenameRule('newname', $_GET['folder']);
// dispatch
if (isset($_GET[SUBMIT])) {
	svnRename(); 
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$file = new SvnOpenFile($target);
	$template->assign_by_ref('file', $file);
	$template->assign('target', $target);
	$template->assign('folder', getParent($target));
	$template->assign('oldname', getPathName($target));
	$template->assign('repository', getRepository());
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function svnRename() {
	Validation::expect('target', 'folder', 'newname', 'message');
	$edit = new SvnEdit('move');
	$oldUrl = getTargetUrl();
	$newUrl = getRepository().$_GET['folder'].$_GET['newname'];
	if (isset($_GET['message'])) {
		$edit->setMessage($_GET['message']);
	}
	$edit->addArgUrl($oldUrl);
	$edit->addArgUrl($newUrl);
	$edit->exec();
	displayEdit(Presentation::getInstance());
}
?>