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
	$file->isWritable(); // check before page is displayed because it might require authentication
	$template->assign_by_ref('file', $file);
	$template->assign('target', $target);
	$folder = getParent($target);
	if (!$folder) $folder = '/'; // getParent resturns empty string for file in root
	$template->assign('folder', $folder);
	$template->assign('oldname', getPathName($target));
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function svnRename() {
	Validation::expect('target', 'folder', 'newname', 'message');
	$template = Presentation::background();
	$edit = new SvnEdit('move');
	$oldUrl = getTargetUrl();
	$newUrl = getRepository().$_REQUEST['folder'].$_REQUEST['newname'];
	if (isset($_REQUEST['message'])) {
		$edit->setMessage($_REQUEST['message']);
	}
	$edit->addArgUrl($oldUrl);
	$edit->addArgUrl($newUrl);
	$edit->exec();
	displayEdit($template);
}
?>