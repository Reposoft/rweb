<?php
/**
 * Helper to export a folder to local temp and create zip archive.
 * Make sure to call cleanup after file has been sent.
 * @param $folder SvnOpenFile instance
 * @return String path to created zip file
 */
function reposExportZip(/*SvnOpenFile*/ $folder) {
	
	$tmp = System::getTempFolder('downloadzip');
	$tmpe = $tmp.'export';
	$zipfile = $tmp.'reposexport.zip';
	
	// use zip ibrary
	$zip = new ZipArchive();
	
	// export target to temp folder
	$export = new SvnOpen('export');
	// TODO make use of peg revision, this logic fails for folder with revision specified that is no longer in HEAD
	// TODO consider using the same as SvnOpenFile->_specifyUrlAndRev
	$export->addArgRevision($folder->getRevision());
	$export->addArgUrl($folder->getUrl());
	$export->addArgPath($tmpe);
	// set max time for this phase
	//set_time_limit('max_execution_time', 300);
	set_time_limit(300);
	if ($export->exec()) {
		trigger_error('Export of folder fialed. Can not create zip for download.', E_USER_ERROR);
	}
	
	// open archive
	if ($zip->open($zipfile, ZIPARCHIVE::CREATE) !== TRUE) {
		trigger_error('Failed to create temporary archive for download', E_USER_ERROR);
	}
	
	// set max time for zip phase
	set_time_limit(300);
	
	// iterate items in export
	// The iterator behaves a bit oddly, might be safer to read the svn export output
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpe));
	
	// add to archive using relative path, starting with folder name so that file is extracted to single folder
	$entries = 0;
	foreach ($iterator as $key=>$value) {
		$subpath = substr($key, strlen($tmpe));
		if (strEnds($subpath, '/.') || strEnds($subpath, '/..')) continue;
		$path = $folder->getFilename() . $subpath;
		$zip->addFile($key, $path) or trigger_error('Zip error when adding $key', E_USER_ERROR);
		$entries++;
	}
	
	// close and save archive
	$zip->close();
	if (!$entries) return false;
	return $zipfile;
}

function reposExportZipCleanup($zipfile) {
	System::deleteFolder(getParent($zipfile));
}
?>
