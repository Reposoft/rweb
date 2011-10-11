<?php
// get svn blame output

require("../SvnOpen.class.php" );

$url = getTargetUrl();
Validation::expect('target');

$cmd = new SvnOpen('blame');
$cmd->addArgUrl($url);

header('Content-type: text/xml');
$cmd->addArgOption('--xml');

if ($cmd->passthru()) {
	trigger_error(implode("\n",$cmd->getOutput()), E_USER_ERROR);	
}

?>
