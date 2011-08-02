<?php
/**
 * Locks a file in HEAD using target path
 */

// maybe also service to read lock
// { "require" : "yes"; "locked" ; "" }
// locked by
// locked when
require('../../conf/Presentation.class.php');
require('../SvnEdit.class.php');

if (isset($_REQUEST['s'])) trigger_error("Multiple items not supported yet. Got: \n".join(", \n", $_REQUEST['s']));

if ($_SERVER['REQUEST_METHOD']=='POST') {
	Validation::expect('message');
	lock($_POST['message']); 
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

function lock($message) {
	$targeturl = getTargetUrl();
	$p = Presentation::background();
	$lock = new SvnEdit('lock');
	if (isset($_POST['message'])) {
		$lock->setMessage($_POST['message']);
	} else {
		$lock->setMessage("");
	}
	$lock->addArgUrl($targeturl);
	$lock->exec();
	if (isset($_POST['download']) && $_POST['download']) {
		$p->assign('redirect', getWebapp().'open/download/?target='.urlencode(getTarget())
			.(isset($_REQUEST['base']) ? '&base='.$_REQUEST['base'] : ''));
	}
	displayEdit($p, getParent($targeturl));
}

?>
