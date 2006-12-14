<?php
require_once(dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require_once(dirname(dirname(__FILE__))."/SvnOpen.class.php" );

$url = getTargetUrl();
if(empty($url) || !isset($_GET['revfrom']) || !isset($_GET['revto'])) {
	trigger_error("Argument error: target, 'revfrom' and 'revto' must be specified.");
	exit;
}
$revfrom = $_GET['revfrom'];
$revto = $_GET['revto'];

// TODO use SvnOpenFile to check that the mime type is ok for diffing (if not view both or something)

$command = new SvnOpen('diff');
// using peg revision to identify the object, and operativer revision to select the diff
// this operation assumes that the two given revision numbers are for the same object (no moves inbetween)
$command->addArgRevisionRange($revfrom.':'.$revto);
$command->addArgUrlPeg($url, $revfrom);

$p = new Presentation();
$p->assign('target', $url);
$p->assign('revfrom', $revfrom);
$p->assign('revto', $revto);
$referer = getHttpReferer();
if (!empty($referer) && strContains($referer, '/open/log/')) {
	$p->assign('logurl', $referer);
	$p->assign('repository', getRepository().strAfter($referer, 'target='));
} else {
	$existingFolder = login_getFirstNon404Parent(getParent($url), $s);
	$p->assign('repository', $existingFolder);
	$p->assign('logurl', '../log/target='.strAfter($existingFolder, getRepository()));
}

$diffarray = $command->exec();
if($command->getExitcode()) trigger_error("Could not read 'diff' for $url revision $revfrom to $revto. ".implode("\n", $command->getOutput()));
$p->assign('diff', implode("\n",$command->getOutput()));

$p->display();


?>
