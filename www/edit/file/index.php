<?php
/**
 * Edit a file in Repos.
 * Online edit is always based on the revision of the file when the form is loaded.
 * It is always text files, so it is assumed that no locks exist or are needed.
 * @package edit
 */
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));

require("../../conf/Presentation.class.php" );
require("../SvnEdit.class.php" );
require("../../open/SvnOpenFile.class.php");
addPlugin('edit');
addPlugin('password');
//addPlugin('acl');

define('MAX_FILE_SIZE', 1024*1024*10);

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
	$template->assign('maxfilesize',MAX_FILE_SIZE);
	$template->assign('target',$target);
	$template->assign('targeturl', getTargetUrl());
	$template->display();
} else {
	trigger_error('This form should be posted to ../upload/.', E_USER_ERROR);	
}
?>
