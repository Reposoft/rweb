<?php
// TODO convert to the same concept as 'cat'
require_once(dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require_once(dirname(dirname(dirname(__FILE__)))."/account/login.inc.php" );

$url = getTargetUrl();
if(empty($url) || !isset($_GET['rev']) || !isset($_GET['revto'])) {
	trigger_error("Argument error: target, 'rev' and 'revto' must be specified.");
	exit;
}
$revfrom = getRevision();
$revto = getRevision($_GET['revto']);

$revisions = ' -r '.$revfrom.':'.$revto;

$cmd = 'diff '.escapeArgument($url.'@'.$revfrom).' '.escapeArgument($url.'@'.$revto);


$p = new Presentation();
$p->assign('target', $url);
$p->assign('revfrom', $revfrom);
$p->assign('revto', $revto);
$referer = getHttpReferer();
if (!empty($referer) && strContains($referer, '/open/log/')) {
	$p->assign('logurl', $referer);
	$p->assign('repository', getRepository().strAfter($referer, 'target='));
} else {
	$existingFolder = login_getFirstNon404Parent(getParent($url), &$s);
	$p->assign('repository', $existingFolder);
	$p->assign('logurl', '../log/target='.strAfter($existingFolder, getRepository()));
}

$diffarray = login_svnRun($cmd);
$result = array_pop($diffarray);
// TODO handle error codes
$p->assign('diff', implode("\n",$diffarray));

$p->display();


?>
