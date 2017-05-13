<?php
/**
 * Shows a file in browser with filename header.
 *
 * @package open
 */
require("../SvnOpenFile.class.php" );

$revisionRule = new RevisionRule();
// if revision is set it is peg
$file = new SvnOpenFile(getTarget(), $revisionRule->getValue());
if ($file->getStatus() != 200) {
	// TODO have some kind of forwarding to the error pages for matching status code
	require("../../conf/Presentation.class.php");
	trigger_error('Failed to read '.$file->getPath().' from repository (status '.$file->getStatus().
	'). Maybe it exists in a version other than '.$revisionRule->getValue().'.', E_USER_ERROR);
}

if (!$file->isDownloadAllowed()) {
	trigger_error('Download has been disabled at '.$file->getPath(), E_USER_ERROR);
}

// Revision number should be "last changed" so we don't get different downloads for identical file
// This is also the revision number expected by the "based on version" feature in upload changes
// For folders: last changed revision can not be used because subitems might have changed
// TODO this is false, commit revision changes when folder contents change,
//  consider using the same for folders as for files, see also zipfolder export args
$namerev = $file->isFolder() ? $file->getRevision() : $file->getRevisionLastChanged();
$name = $file->getFilenameWithoutExtension() . '(r'.$namerev.')';
if ($file->getExtension()) {
	$name .= '.' . $file->getExtension();
}

// IE6 needs encoded name, other browsers don't like that.
if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
	$name = rawurlencode($name);
}

if ($file->isFolder()) {
	require dirname(__FILE__).'/zipfolder.php';
	$zip = reposExportZip($file);
	if ($zip === false) {
		require_once('../../conf/Presentation.class.php');
		$p = Presentation::getInstance();
		$p->showErrorNoRedirect('Folder '.$file->getFilename().' is empty', 'Failed to create zip');
		exit;
	}
	if (!file_exists($zip)) {
		trigger_error('Zip error.', E_USER_ERROR);
		exit;
	}
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
