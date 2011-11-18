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

targetLogin(); // edit operation can not be public

if ($_SERVER['REQUEST_METHOD']=='POST') {
	Validation::expect('message');
	lock($_POST['message']); 
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

function detectLockWarning($arrSvnOutput) {
	$msg = $arrSvnOutput[0];
	// svn: warning: Path '/f.txt' is already locked by user 'test' in filesystem '/.../svn/demo1/db'
	// the lock message missing in the output
	if (preg_match('/.*warning:.*already locked by user \'([^\']+)\'.*/', $msg, $match)) {
		$current = getReposUser();
		if ($current == $match[1]) {
			Validation::error('The file is already locked by you.');
		} else {
			header('X-Repos-LockedBy', $match[1]);
			Validation::error('The file is already locked by user '.$match[1].'.'); // Could use different status code, for example 403, but there is no framework support for that
		}
	}
}

function lock($message) {
	$targeturl = getTargetUrl();
	$lock = new SvnEdit('lock');
	if (isset($_POST['message'])) {
		$lock->setMessage($_POST['message']);
	} else {
		$lock->setMessage("");
	}
	$lock->addArgUrl($targeturl);
	$lock->exec();
	detectLockWarning($lock->getOutput()); // some errors, like locked by someone else, still have exit code 0
	$p = Presentation::background();
	if (isset($_POST['download']) && $_POST['download']) {
		$p->assign('redirect', getWebapp().'open/download/?target='.urlencode(getTarget())
			.(isset($_REQUEST['base']) ? '&base='.$_REQUEST['base'] : ''));
	}
	displayEdit($p, getParent($targeturl));
}

?>
