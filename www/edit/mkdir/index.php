<?php

require("../../conf/Presentation.class.php" );
require("../SvnEdit.class.php" );	
addPlugin('validation');

// automatic validation
new FilenameRule('name');
// svn import: parent folder must exists, to avoid implicit create
$parent = new ResourceExistsRule('target');
// explicit validation
new NewFilenameRule('name', $parent->getValue());

if (isset($_REQUEST[SUBMIT])) {
	Validation::expect('target', 'name', 'message');
	createNewFolder($_REQUEST['name'],$_REQUEST['message']); 
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
	$tmp = System::getTempFolder('emptyfolders');
	$edit = new SvnEdit('import');
	$edit->setMessage($message);
	$edit->addArgPath($tmp);
	$edit->addArgUrl($newurl);
	$edit->exec();
	System::deleteFolder($tmp);
	displayEdit($template, getTargetUrl());
}

?>
