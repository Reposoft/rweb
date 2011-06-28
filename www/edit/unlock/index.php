<?php
/**
 * Unlock a file.
 *
 * @package
 */
require('../../conf/Presentation.class.php');
require('../SvnEdit.class.php');

if (isset($_REQUEST['s'])) trigger_error("Multiple items not supported yet. Got: \n".join(", \n", $_REQUEST['s']));

if ($_SERVER['REQUEST_METHOD']=='POST') {
	$template = Presentation::getInstance();
	$targeturl = getTargetUrl();
	$unlock = new SvnEdit('unlock');
	$unlock->addArgUrl($targeturl);
	$unlock->exec();
	displayEdit($template, getParent($targeturl));
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$template->assign('target', $target);
	$template->assign('folderurl', getRepository().getParent($target));
	if (isset($_GET['download'])) {
		$template->assign('download', 1);
	}
	$template->display();
}

?>
