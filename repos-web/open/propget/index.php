<?php
header('Content-Type: text/plain');
require("../SvnOpen.class.php" );
$revisionRule = new RevisionRule();
Validation::expect('target', 'propname');
$url = getTargetUrl();
$cmd = new SvnOpen('propget');
$cmd->addArg($_REQUEST['propname']);
if ($revisionRule->getValue()) {
	$cmd->addArgUrlPeg($url, $revisionRule->getValue());
} else {
	$cmd->addArgUrl($url);
}
if ($cmd->exec()) { // Passhtrough does not work with authentication
	trigger_error(implode("\n",$cmd->getOutput()), E_USER_ERROR);
}
$val = $cmd->getOutput();
if (is_array($val)) {
	$val = count($val) ? $val[0] : '';
}
if (!$val) {
	header('HTTP/1.0 404 Not Found');
}
if ($revisionRule->getValue()) {
	header('Cache-Control: max-age=8640000');
}
header('Content-Length: '.strlen($val));
echo $val;
?>