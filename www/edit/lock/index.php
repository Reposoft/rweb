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
addPlugin('validation');

if (isset($_GET[SUBMIT])) {
	Validation::expect('message');
	lock($_GET['message']); 
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$template->assign('target', $target);
	$template->assign('repository', getRepository().getParent($target));
	if (isset($_GET['download'])) {
		$template->assign('download', 1);
	}
	$template->display();
}

function lock($message) {
	$targeturl = getTargetUrl();
	$lock = new SvnEdit('lock');
	if (isset($_GET['message'])) {
		$lock->setMessage($_GET['message']);
	} else {
		$lock->setMessage("");
	}
	$lock->addArgUrl($targeturl);
	$lock->exec();
	$p = Presentation::getInstance();
	if (isset($_GET['download']) && $_GET['download']) {
		$p->assign('redirect', getWebapp().'open/download/?target='.urlencode(getTarget()));
	}
	displayEdit($p, getParent($targeturl));
}

?>