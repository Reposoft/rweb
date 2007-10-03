<?php
/**
 * Edit a file in Repos.
 * Online edit is always based on the revision of the file when the form is loaded.
 * It is always text files, so it is assumed that no locks exist or are needed.
 * @package edit
 */
require("../../conf/Presentation.class.php" );
require("../SvnEdit.class.php" );
addPlugin('edit');
addPlugin('password');
addPlugin('filename');
addPlugin('acl');

// name only exists for new files, not for new version requests
new FilenameRule("name");
new NewFilenameRule("name", getTarget());
new EditTypeRule('type'); // type is not required for this page, but it is for the form action page

if ($_SERVER['REQUEST_METHOD']=='GET') {
	$template = Presentation::getInstance();
	$target = getTarget();
	$targeturl = getTargetUrl();
	if (isFile($target)) {
		$file = new SvnOpenFile($target);
		$template->assign_by_ref('file', $file);
		$template->assign('repository', getParent($targeturl));
		// old file has type=extension
		$template->assign('type', $file->getExtension());
	} else {
		$template->assign('repository', $targeturl);
		// new file has a default type wich can be changed with query param
		$template->assign('type', isset($_GET['type']) ? $_GET['type'] : 'txt');
	}
	$template->assign('target',$target);
	$template->assign('targeturl', getTargetUrl());
	$template->display();
} else {
	trigger_error('This form should be posted to ../upload/.', E_USER_ERROR);	
}
?>
