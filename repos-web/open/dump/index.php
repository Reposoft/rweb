<?php
header('Content-Type: text/plain');

require("../SvnOpen.class.php" );

$revisionRule = new RevisionRule();
Validation::expect('target');

$url = getTargetUrl();
// no auth with: $cmd = new Command('svnrdump');
$cmd = new SvnOpen('dump');

if ($revisionRule->getValue()) {
  $cmd->addArgOption('--incremental');
	$cmd->addArgOption('-r', $revisionRule->getValue() . ':HEAD');
}

$cmd->addArgUrl(rtrim(getTargetUrl(), '/'));

// Note that passthru probably won't support auth detection+prompt
if ($cmd->passthru()) {
	trigger_error(implode("\n",$cmd->getOutput()), E_USER_ERROR);
}
?>
