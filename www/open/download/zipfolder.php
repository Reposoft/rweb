<?php
/**
 * Helper to export a folder to local temp and create zip archive.
 * Make sure to call cleanup after file has been sent.
 * @param $folder SvnOpenFile instance
 * @return String path to created zip file
 */
function reposExportZip(/*SvnOpenFile*/ $folder) {
	
	$tmp = System::getTempFolder('downloadzip');
	$zipfile = $tmp.'reposexport.zip';
	
	// use zip ibrary
	$zip = new ZipArchive();
	// open archive
	if ($zip->open($zipfile, ZIPARCHIVE::CREATE) !== TRUE) {
		trigger_error('Failed to create temporary archive for download', E_USER_ERROR);
	}	
	
	// add entries
	$rev = $folder->getRevision();
	$entries = 0;
	if (is_a($folder, 'SvnOpenFileMulti') && $folder->isMulti()) {
		$entries += reposExportZipMulti($zip, $tmp, $folder->getMultiPathsFromRoot(),
			$rev, $folder->getPath(), $folder->getFilename().'(sparse,r'.$rev.')');
	} else {
		// all contents of folder
		$entries += reposExportZipAddFolder($zip, $tmp, $folder->getUrl(), $rev, $folder->getFilename()); 
	}
	
	// close and save archive
	$zip->close();
	if (!$entries) return false;
	return $zipfile;
}

function reposExportZipMulti($zip, $tmp, $paths, $rev, $start, $topname) {
	// sparse export to zip
	if (!strEnds($start, '/')) {
		trigger_error("Expected target to be folder ending with slash, got: $start", E_USER_ERROR);
	}
	$entries = 0;
	foreach ($paths as $p) {
		$key = $topname.'/'.substr($p, strlen($start));
		if (strEnds($p, '/')) {
			$entries += reposExportZipAddFolder($zip, $tmp, getTargetUrl($p), $rev, $key);
		} else {
			$entries += reposExportZipAddFile($zip, $tmp, getTargetUrl($p), $rev, $key);
		}
	}
	return $entries;
}

function reposExportZipAddFile($zip, $tmp, $fileUrl, $fileRev, $basePath) {
	$tmpe = $tmp.'export'.microtime().rand();
	// export target to temp folder
	$export = new SvnOpen('export');
	$export->addArgUrlPeg($fileUrl, $fileRev);
	$export->addArgPath($tmpe);
	
	// set max time for file export phase
	set_time_limit(300);
	if ($export->exec()) {
		trigger_error("Export of $fileUrl@$fileRev failed. Zip operation aborted.", E_USER_ERROR);
	}
	
	// set max time for zip phase
	set_time_limit(300);
	
	$zip->addFile($tmpe, $basePath) or trigger_error('Zip error when adding $basePath', E_USER_ERROR);
	return 1;
}

/**
 * 
 * @param filehandle $zip Open zip handle
 * @param String $tmp temp folder for the zip operation wher exports can be placed
 * @param String $folderUrl The repository resource to add to the zip
 * @param String|int $folderRev The revision at which the folderUrl exists and should be traversed
 * @param String $basePath What the folder should be called in the archive, optionally a path with no leading slash
 * @return int The number of entries added to the zip
 */
function reposExportZipAddFolder($zip, $tmp, $folderUrl, $folderRev, $basePath) {
	$tmpe = $tmp.'export'.microtime().rand();
	// export target to temp folder
	$export = new SvnOpen('export');
	$export->addArgUrlPeg($folderUrl, $folderRev);
	$export->addArgPath($tmpe);
	
	// set max time for folder export phase
	set_time_limit(300);
	if ($export->exec()) {
		trigger_error("Export of folder $folderUrl@$folderRev failed. Can not create zip for download.", E_USER_ERROR);
	}
	
	// set max time for zip phase
	set_time_limit(300);
	
	// iterate items in export
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpe));
	
	// add to archive using relative path, starting with folder name so that file is extracted to single folder
	$entries = 0;
	foreach ($iterator as $fpath=>$value) {
		// trimming trailing slash is new in 1.4
		$path = $basePath . rtrim(substr($fpath, strlen($tmpe)), '/');
		$zip->addFile($fpath, $path) or trigger_error('Zip error when adding $path', E_USER_ERROR);
		$entries++;
	}
	return $entries;
}

function reposExportZipCleanup($zipfile) {
	System::deleteFolder(getParent($zipfile));
}
?>
