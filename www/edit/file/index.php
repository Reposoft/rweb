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

// password file editor
if (strEnds(getTarget(), 'repos.user')) {
	header('Cache-Control: no-cache');
	$url = asLink(getWebapp().'account/password/?target='.urlencode(getTarget()));
	if (strpos(getSelfUrl(),'/edit/') === false) $url .= '&view=1';
	header('Location: '.$url);
}

// name only exists for new files, not for new version requests
new FilenameRule("name");
new NewFilenameRule("name", getTarget());
new EditTypeRule('type'); // type is not required for this page, but it is for the form action page

if ($_SERVER['REQUEST_METHOD']=='GET' || $_SERVER['REQUEST_METHOD']=='HEAD') {
	new ResourceExistsRule();
	Validation::expect();
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
