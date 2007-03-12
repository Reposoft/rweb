<?php
/**
 * Shows a file in browser with filename header.
 *
 * @package open
 */
require("../SvnOpenFile.class.php" );

$revisionRule = new RevisionRule();

$file = new SvnOpenFile(getTarget(), $revisionRule->getValue());
if ($file->getStatus() != 200) {
	// TODO have some kind of forwarding to the error pages for matching status code
	require("../../conf/Presentation.class.php");
	trigger_error('Failed to read '.$file->getPath().' from repository (status '.$file->getStatus().
	'). Maybe it exists in a version other than '.$file->getRevision().'.', E_USER_ERROR);
}

$name = $file->getFilename();
$dot = strrpos($name, '.');
$name = substr($name, 0, $dot).'-'.$file->getRevision().substr($name,$dot);

// IE6 needs encoded name, other browsers don't like that.
if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
	$name = rawurlencode($name);
}

header('Content-Type: '.$file->getType());
header('Content-Length: '.$file->getSize());
header('Content-Disposition: attachment; filename="'.$name.'"');

$file->sendInline();

?>
