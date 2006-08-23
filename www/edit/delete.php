<?php
require( dirname(dirname(__FILE__))."/conf/Presentation.class.php" );
require( dirname(__FILE__)."/edit.class.php" );

if (isset($_GET['message'])) {
	delete($_GET['message']); 
} else {
	$target = rtrim(getTarget(),'/'); //folder should also be without tailing slash
	$template = new Presentation();
	$template->assign('target', $target);
	$template->assign('targetname', basename($target));
	$template->assign('parent', dirname($target));
	$template->assign('repo', getRepositoryUrl());
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function delete($message) {
	$edit = new Edit('delete');
	$edit->setMessage($message);
	$edit->addArgument(getTargetUrl());
	$edit->execute();
	$edit->present(new Presentation(), dirname(rtrim(getTargetUrl(),'/')));
}
?>