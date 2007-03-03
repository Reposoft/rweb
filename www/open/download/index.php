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
	trigger_error('Failed to read the file from repository (status '.$file->getStatus().
	'). Maybe it does not exist in version '.$revisionRule->getValue().'.', E_USER_ERROR);
}

$name = $file->getFilename();
$dot = strrpos($name, '.');
$name = substr($name, 0, $dot).'-'.$file->getRevision().substr($name,$dot);

header('Content-Type: '.$file->getType());
header('Content-Length: '.$file->getSize());
header('Content-Disposition: attachment; filename="'.$name.'"');

$file->sendInline();

?>
