<?php

require("../../conf/Presentation.class.php" );
require("../SvnEdit.class.php" );	
addPlugin('validation');

// automatic validation
new FilenameRule('name');
// explicit validation
new NewFilenameRule('name', getTarget());

if (isset($_GET[SUBMIT])) {
	Validation::expect('target', 'name', 'message');
	createNewFolder($_GET['name'],$_GET['message']); 
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$template->assign('target', $target);
	$template->assign('repository', getRepository().$target);
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function createNewFolder($name, $message) {
	global $folderRule;
	$template = Presentation::getInstance();
	$newurl = getTargetUrl().$name;
	$dir = tmpdir();
	$edit = new SvnEdit('import');
	$edit->setMessage($message);
	$edit->addArgPath($dir);
	$edit->addArgUrl($newurl);
	$edit->exec();
	System::deleteFolder($dir);
	displayEdit($template, getTargetUrl());
}

// Creates a directory with a unique name
// at the specified with the specified prefix.
// Returns directory name on success, false otherwise
function tmpdir()
{
	return System::getTempFolder('emptyfolders');
}
?>