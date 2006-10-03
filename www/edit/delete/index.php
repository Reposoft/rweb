<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/edit.class.php" );

if (isset($_GET[SUBMIT])) {
	Validation::expect('message');
	delete($_GET['message']); 
} else {
	$target = getTarget();
	$template = new Presentation();
	$template->assign('target', $target);
	$template->assign('repository', getRepository().getParent($target));
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function delete($message) {
	$edit = new Edit('delete');
	$edit->setMessage($message);
	$edit->addArgUrl(getTargetUrl());
	$edit->execute();
	$edit->present(new Presentation(), dirname(rtrim(getTargetUrl(),'/')));
}
?>