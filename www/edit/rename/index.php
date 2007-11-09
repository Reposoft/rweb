<?php
require("../../conf/Presentation.class.php" );
require("../SvnEdit.class.php" );
addPlugin('validation');
addPlugin('filename');

// automatic validation
new FilenameRule('newname');
// svn import: parent folder must exists, to avoid implicit create
$parent = new ResourceExistsRule('folder');
// explicit validation
new NewFilenameRule('newname', $parent->getValue());

// dispatch
if ($_SERVER['REQUEST_METHOD']=='POST') {
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
	$newUrl = getRepository().$_REQUEST['folder'].$_REQUEST['newname'];
	if (isset($_REQUEST['message'])) {
		$edit->setMessage($_REQUEST['message']);
	}
	$edit->addArgUrl($oldUrl);
	$edit->addArgUrl($newUrl);
	$edit->exec();
	displayEdit(Presentation::getInstance());
}
?>