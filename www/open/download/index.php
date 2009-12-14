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
	'). Maybe it exists in a version other than '.$revisionRule->getValue().'.', E_USER_ERROR);
}

$name = $file->getFilename();
$dot = strrpos($name, '.');
if (!$dot) $dot = strlen($name);
// revision number should be "last changed" so we don't get different downloads for identical file
$name = substr($name, 0, $dot).'(r'.$file->getRevisionLastChanged().')'.substr($name,$dot);

// IE6 needs encoded name, other browsers don't like that.
if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
	$name = rawurlencode($name);
}

if ($file->isFolder()) {
	require dirname(__FILE__).'/zipfolder.php';
	$zip = reposExportZip($file);
	header('Content-Type: application/zip');
	header('Content-Length: '.filesize($zip));
	header('Content-Disposition: attachment; filename="'.$name.'.zip"');
	$z = fopen($zip, 'rb');
	fpassthru($z);
	fclose($z);
	reposExportZipCleanup($zip);
	exit;
}

header('Content-Type: '.$file->getType());
// FIXME this size will be incorrect, and truncate the file, 
// if there are svn:keyword insertions //header('Content-Length: '.$file->getSize());
// (SvnOpenFile uses 'svn cat'; passthru or incremental sending is needed to save memory)
header('Content-Disposition: attachment; filename="'.$name.'"');

$file->sendInline();

?>
