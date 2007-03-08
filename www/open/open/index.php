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
if ($file->isFolder()) {
	$rev = $_GET['rev'];
	$list = getWebapp().'open/list/?target='.rawurlencode($target).'&rev='.$rev;
	header('Location: '.$list);
	exit;
}

header('Content-Type: '.$file->getType());
header('Content-Length: '.$file->getSize());
header('Content-Disposition: inline; attachment; filename="'.$file->getFilename().'"');

$file->sendInline();

?>
