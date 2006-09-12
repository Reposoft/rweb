<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/edit.class.php" );

if (isset($_GET['newname'])) {
	svnRename(); 
} else {
	$template = new Presentation();
	$template->assign('target', getTarget());
	$template->assign('repo', getRepositoryUrl());
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function svnRename() {
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