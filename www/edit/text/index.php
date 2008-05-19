<?php
/**
 * Edit a file in Repos.
 * Online edit is always based on the revision of the file when the form is loaded.
 * It is always text files, so it is assumed that no locks exist or are needed.
 * @package edit
 */
require("../../conf/Presentation.class.php" );
require("../SvnEdit.class.php" );
require('EditTypeRule.class.php');//addPlugin('edit');

// name only exists for new files, not for new version requests
new FilenameRule("name");
new NewFilenameRule("name", getTarget());
new EditTypeRule('type'); // type is not required for this page, but it is for the form action page

if ($_SERVER['REQUEST_METHOD']=='GET' || $_SERVER['REQUEST_METHOD']=='HEAD') {
	new ResourceExistsRule();
	$template = Presentation::getInstance();
	$target = getTarget();
	$targeturl = getTargetUrl();
	if (isFile($target)) {
		header("Cache-Control: no-cache, must-revalidate"); // the hidden field fromrev MUST be updated after successful POST to avoid strange conflicts - disable caching until we have a better solution
		$template->assign('isfile', true);
		$file = new SvnOpenFile($target);
		$template->assign_by_ref('file', $file);
		$template->assign('repository', getParent($targeturl));
		// old file has type=extension
		$template->assign('type', $file->getExtension());
	} else {
		$template->assign('isfile', false);
		$template->assign('repository', $targeturl);
		// new file has a default type wich can be changed with query param
		$template->assign('type', isset($_GET['type']) ? $_GET['type'] : 'txt');
		$template->assign('suggestname', $_GET['suggestname'] ? $_GET['suggestname'] : '');
	}
	$template->assign('target',$target);
	$template->assign('targeturl', getTargetUrl());
	$template->display();
} else {
	trigger_error('This form should be posted to ../upload/.', E_USER_ERROR);
}
?>
