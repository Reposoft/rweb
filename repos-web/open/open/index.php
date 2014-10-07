<?php
/**
 * Shows a file in browser with filename header.
 *
 * @package open
 */
require("../SvnOpenFile.class.php" );

$revisionRule = new RevisionRule();

$target = getTarget();
$file = new SvnOpenFile($target, $revisionRule->getValue());
// TODO seems like isFoder does not return true for added folder link from log
if ($file->isFolder()) {
	// The logic for folders is in main open/.
	// Causes double redirect for targets that don't end with slash, but that's ok.
	$rev = $_GET['rev'];
	$list = getWebapp().'open/?target='.rawurlencode($target).'&rev='.$rev;
	header('Location: '.$list);
	exit;
}

if ($file->getType() == "text/html"){
	header('Location: '.$file->url);
	die();
} else {
	header('Content-Type: '.$file->getType());
	header('Content-Length: '.$file->getSize());
	header('Content-Disposition: inline; attachment; filename="'.$file->getFilename().'"');
	$file->sendInline();
}

?>
