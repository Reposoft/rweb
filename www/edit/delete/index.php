<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/SvnEdit.class.php" );

if (isset($_GET[SUBMIT])) {
	Validation::expect('message');
	delete($_GET['message']); 
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$template->assign('target', $target);
	$template->assign('repository', getRepository().getParent($target));
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function delete($message) {
	$edit = new SvnEdit('delete');
	$edit->setMessage($message);
	$edit->addArgUrl(getTargetUrl());
	$edit->exec();
	$edit->present(Presentation::getInstance(), dirname(rtrim(getTargetUrl(),'/')));
}
?>