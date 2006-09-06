<?php
require( dirname(dirname(__FILE__))."/conf/Presentation.class.php" );
require( dirname(__FILE__)."/edit.class.php" );

if (isset($_GET['name'])) {
	createFolder(getTargetUrl(),$_GET['name'],$_GET['message']); 
} else {
	$target = getTarget();
	$template = new Presentation();
	$template->assign('target', $target);
	$template->assign('repo', getRepositoryUrl());
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function createFolder($parentUri, $name, $message) {
	$template = new Presentation();
	$newfolder = rtrim($parentUri,'/').'/'.urlEncodeNames($name);
	$dir = tmpdir();
	if (!$dir) {
		$template->trigger_error("Could not create temporary directory");
	}
	$edit = new Edit('import');
	$edit->setMessage($message);
	$edit->addArgPath($dir, true);
	$edit->addArgUrl($newfolder);
	$edit->execute();
	//rmdir($dir);
	$edit->present($template, getTargetUrl());
}

// Creates a directory with a unique name
// at the specified with the specified prefix.
// Returns directory name on success, false otherwise
function tmpdir()
{
	return getTempnamDir('emptyfolders');
}
?>