<?php
require_once(dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require_once(dirname(dirname(__FILE__))."/SvnOpen.class.php" );
addPlugin('syntax');

$url = getTargetUrl();

Validation::expect('rev', 'fromrev');
$revRule = new RevisionRule();
$fromrevRule = new RevisionRule('fromrev');
$revto = $revRule->getValue();
$revfrom = $fromrevRule->getValue();

// TODO use SvnOpenFile to check that the mime type is ok for diffing (if not view both or something)

$command = new SvnOpen('diff');
// using peg revision to identify the object, and operativer revision to select the diff
// this operation assumes that the two given revision numbers are for the same object (no moves inbetween)
$command->addArgRevisionRange($revfrom.':'.$revto);
$command->addArgUrlPeg($url, $revfrom);

$p = Presentation::getInstance();
$p->assign('target', getTarget());
$p->assign('revfrom', $revfrom);
$p->assign('revto', $revto);
$existingFolder = login_getFirstNon404Parent(getParent($url), $s);
$p->assign('repository', $existingFolder);
$referer = getHttpReferer();
if (!empty($referer) && strContains($referer, '/open/log/')) {
	$p->assign('logurl', $referer);
} else {
	// best effort if user came through a bookmark
	$p->assign('logurl', '../log/?target='.strAfter($existingFolder, getRepository()));
}

$diffarray = $command->exec();
if($command->getExitcode()) trigger_error("Could not read 'diff' for $url revision $revfrom to $revto. ".implode("\n", $command->getOutput()));
$p->assign('diff', implode("\n",$command->getOutput()));

$p->display();


?>
