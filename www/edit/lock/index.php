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

if (isset($_GET[SUBMIT])) {
	Validation::expect('message');
	lock($_GET['message']); 
} else {
	$target = getTarget();
	$template = new Presentation();
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
	$p = new Presentation();
	$p->assign('redirect', getWebapp().'open/download/?target='.getTarget());
	$lock->present($p, getParent($targeturl));
}

?>