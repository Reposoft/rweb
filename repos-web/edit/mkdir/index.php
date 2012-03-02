<?php

require("../../conf/Presentation.class.php" );
require("../SvnEdit.class.php" );

targetLogin(); // edit operation can not be public

if ($_SERVER['REQUEST_METHOD']=='POST') {
	createNewFolder($_REQUEST['name'],$_REQUEST['message']); 
} else {
	$target = getTarget();
	// use SvnOpenFile to check write access before showing form
	$folder = new SvnOpenFile($target);
	if (!$folder->isWritable()) {
		trigger_error('Write access denied', E_USER_NOTICE);
	}
	$template = Presentation::getInstance();
	$template->assign('target', $target);
	$template->assign('folderurl', getRepository().$target);
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function createNewFolder($name, $message) {
	// automatic validation
	new FilenameRule('name');
	// svn import: parent folder must exists, to avoid implicit create
	$parent = new ResourceExistsAndIsWritableRule();
	// explicit validation
	new NewFilenameRule('name', $parent->getValue());
	// check required fields and process
	Validation::expect('target', 'name', 'message');
	$template = Presentation::background();
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
