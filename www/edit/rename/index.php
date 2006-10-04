<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/edit.class.php" );

// rules, except target which is automatically validated
new FilenameRule('newname');
new NewFilenameRule('newname');

// dispatch
if (isset($_GET[SUBMIT])) {
	svnRename(); 
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

function svnRename() {
	Validation::expect('target', 'newname', 'message');
	$edit = new Edit('move');
	$targetUrl = getTargetUrl();
	$newUrl = str_replace(basename($_GET['target']), $_GET['newname'], $targetUrl);
	if (isset($_GET['message'])) {
		$edit->setMessage($_GET['message']);
	}
	$edit->addArgUrl($targetUrl);
	$edit->addArgUrl($newUrl);
	$edit->execute();
	$edit->present(new Presentation(), dirname(rtrim(getTargetUrl(),'/')));
}
?>