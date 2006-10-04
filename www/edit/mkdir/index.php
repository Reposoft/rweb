<?php

require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/edit.class.php" );	

new FilenameRule('name');
new NewFilenameRule('name');
// message and target have default rules

if (isset($_GET[SUBMIT])) {
	Validation::expect('target', 'name', 'message');
	createFolder(getTargetUrl(),$_GET['name'],$_GET['message']); 
} else {
	$target = getTarget();
	if (strlen($target) < 1) {
		trigger_error("Path parameter not set."); exit;
	}
	$template = new Presentation();
	$template->assign('target', $target);
	$template->assign('repository', getRepository().$target);
	$template->display();
}

// escaping and unescaping of parameters should _only_
// be done in common helper files (shared classes)
// it is also allowed in templates for presentation, but not in field values

function createFolder($parentUri, $name, $message) {
	$template = new Presentation();
	$newfolder = rtrim($parentUri,'/').'/'.$name;
	$dir = tmpdir();
	if (!$dir) {
		$template->trigger_error("Could not create temporary directory");
	}
	$edit = new Edit('import');
	$edit->setMessage($message);
	$edit->addArgPath($dir, true);
	$edit->addArgUrl($newfolder);
	$edit->execute();
	removeTempDir($dir);
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