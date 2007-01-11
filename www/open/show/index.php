<?php
/**
 * Shows a file in browser with filename header.
 *
 * @package open
 */
require("../SvnOpenFile.class.php" );

$revisionRule = new RevisionRule();

$file = new SvnOpenFile(getTarget(), $revisionRule->getValue());
 
header('Content-Type: '.$file->getType());
header('Content-Length: '.$file->getSize());
header('Content-Disposition: inline; attachment; filename="'.$file->getFilename().'"');

$file->sendInline();

?>
