<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/SvnEdit.class.php" );

targetLogin(); // edit operation can not be public

if ($_SERVER['REQUEST_METHOD']=='POST') {
	delete($_REQUEST['message']); 
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$file = new SvnOpenFile($target);
	$file->isWritable(); // check before page is displayed because it might require authentication
	$template->assign_by_ref('file', $file);
	$template->assign('target', $target);
	$template->assign('folderurl', getRepository().getParent($target));
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function delete($message) {
	Validation::expect('target','message');
	$edit = new SvnEdit('delete');
	$edit->setMessage($message);
	$edit->addArgUrl(getTargetUrl());
	$edit->addArgRevpropsFromPost();
	$edit->exec();
	$template = Presentation::background();
	displayEdit($template, dirname(rtrim(getTargetUrl(),'/')), false);
}
?>