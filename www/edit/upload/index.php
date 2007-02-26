<?php
/**
 * Upload new version of file
 * @package edit
 */
require("../../conf/Presentation.class.php");
require("../SvnEdit.class.php");
require("../../open/SvnOpenFile.class.php");
require("mimetype.inc.php");
addPlugin('edit');

define('MAX_FILE_SIZE', 1024*1024*10);

// name only exists for new files, not for new version requests
new FilenameRule("name");
new EditTypeRule('type');

if (!isTargetSet()) {
	trigger_error("Could not upload file. It is probably larger than ".formatSize(MAX_FILE_SIZE), E_USER_ERROR);
	exit;
} else {
	new NewFilenameRule("name", getTarget());
	
	if ($_SERVER['REQUEST_METHOD']=='GET') {
		showUploadForm();
	} else {
		$upload = new Upload('userfile');
		if ($upload->isCreate()) {
			processNewFile($upload);
		} else {
			processNewVersion($upload);
		}
	}
}

/**
 * Writes textarea contents to version controlled text file.
 * Based on the 'type' parameter value, a custom write function "editWriteNewVersion_$type"
 * is called if it exists. If no custom function is found, the posted string is written with
 * a single fwrite to the file.
 *
 * @param String $postedText the contents from the text area
 * @param String $destinationFile the local working copy file,
 * 	containing the "based-on" revision, or for new files empty
 * @param String $type a type as validated by the 'edit' plugin.
 * @return int the number of bytes in the new version of the file
 * @package edit
 */
function editWriteNewVersion(&$postedText, $destinationFile, $type) {
	if ($type && function_exists('editWriteNewVersion_'.$type)) {
		return call_user_func('editWriteNewVersion_'.$type, $postedText, $destinationFile);
	}
	return _defaultWriteNewVersion($postedText, $destinationFile);
}

/**
 * Writes $postedText as it is to $destinationFile, returns length of $postedText.
 * @see editWriteNewVersion
 */
function _defaultWriteNewVersion(&$postedText, $destinationFile) {
	$fp = fopen($destinationFile, 'w+');
	if ($fp) {
		fwrite($fp, $postedText);
		fclose($fp);
	} else {
		trigger_error("Couldn't write file contents from the submitted text.", E_USER_ERROR);
	}
	return strlen($postedText);
}

/**
 * Plaintext documents are _always_ written in UTF-8 with newline at end of file.
 * We assume that all users have text editors capable of open and resave in UTF-8.
 *
 * @param String $postedText the contents from the text area
 * @param String $destinationFile the local working copy file, currently containing the "based-on" revision
 * @param String $type a type as validated by the 'edit' plugin.
 * @return int the number of bytes in the new version of the file
 * @package edit
 */
function editWriteNewVersion_txt(&$postedText, $destinationFile) {
	// already made UTF-8 by browser encoding
	if (!strEnds($postedText, "\n")) $postedText.="\n";
	return _defaultWriteNewVersion($postedText, $destinationFile);
}

/**
 * Reads a summary of the svn log
 * @return array[int revision => array]
 */
function getLog($targetUrl) {
	$limit = 10;
	$svnlog = new SvnOpen('log');
	$svnlog->addArgOption('-q');
	$svnlog->addArgOption('--limit', $limit, false);
	$svnlog->addArgUrl($targetUrl);
	$svnlog->exec();
	if ($svnlog->getExitcode()) trigger_error("Could not read history for $targetUrl", E_USER_ERROR);
	$log = $svnlog->getOutput();
	$pattern = '/^r(\d+)\s+\|\s+(.*)\s+\|\s+(\d{4}-\d{2}-\d{2}).(\d{2}:\d{2}:\d{2})\s([+-]?\d{2})(\d{2})/';
	$result = array();
	for ($i = 0; $i<count($log); $i++) {
		if (strContains($log[$i],'---')) continue;
		if (preg_match($pattern, $log[$i], $m)) {
			$result[$m[1]] = array('rev'=>$m[1], 'user'=>$m[2], 'date'=>$m[3], 'time'=>$m[4], 'z'=>$m[5].':'.$m[6]); 
		} else {
			trigger_error("invalid log line $i: $log[$i]", E_USER_ERROR);
		}
	}
	return $result;
}
	
function showUploadForm() {	
	$template = Presentation::getInstance();
	$target = getTarget();
	$targeturl = getTargetUrl();
	// if target is a file then this is upload new version
	$isfile = isFile($target);
	if ($isfile) {
		$file = new SvnOpenFile($target);
		$template->assign_by_ref('file', $file);
		$log = getLog($file->getUrl());
		$template->assign_by_ref('log', $log);
		$template->assign('repository', getParent($targeturl));
	} else {
		$template->assign('repository', $targeturl);
	}
	$template->assign('maxfilesize',MAX_FILE_SIZE);
	$template->assign('isfile',$isfile);
	$template->assign('target',$target);
	if (isset($_GET['download'])) {
		// needs an absolute url for meta refresh
		$template->assign('download', getWebapp().'open/download/?target='.urlencode(getTarget()));
	}
	$template->display();
}

/**
 * @param Upload $upload
 */
function processNewFile($upload) {
	$presentation = Presentation::getInstance();
	Validation::expect('name', 'type');
	$newfile = System::getTempFile('upload');
	$upload->processSubmit($newfile);
	$edit = new SvnEdit('import');
	$edit->addArgPath($newfile);
	$edit->addArgUrl($upload->getTargeturl());
	$edit->setMessage($upload->getMessage());
	$edit->exec();
	// detect mime type, if we want to set the property we need to checkout
	$clientMime = $upload->getType();
	// need the original filename, $recommend = getSpecificMimetype($newfile, $clientMime);
	$recommend = getSpecificMimetype($upload->getName(), $clientMime);
	if ($recommend) {
		// TODO echo('Recommending mime type '.$clientMime); exit;
	}
	// clean up
	System::deleteFile($newfile);
	$upload->cleanUp();
	// show results
	displayEdit($presentation, dirname($upload->getTargetUrl()));
}

/**
 * Commits a new version of an existing file.
 * @param Upload $upload the file upload handler
 */
function processNewVersion($upload) {
	Validation::expect('fromrev', 'type');
	$presentation = Presentation::getInstance();
	$dir = System::getTempFolder('upload');
	$repoFolder = dirname($upload->getTargetUrl());
	// check out existing files of the given revision
	$fromrev = $upload->getFromrev();
	$checkout = new SvnEdit('checkout');
	$checkout->addArgOption('--non-recursive');
	if ($fromrev) $checkout->addArgOption('-r', $fromrev, false);
	$checkout->addArgUrl($repoFolder);
	$checkout->addArgPath($dir);
	$checkout->exec();
	if (!$checkout->isSuccessful()) {
		$presentation->showError("Could not read current version of file "
			.$upload->getTargetUrl().". ".$checkout->getResult());
	}
	// upload file to working copy
	$filename = $upload->getName();
	$updatefile = toPath($dir . $filename);	// file_exists needs the argument in ISO-8859-1 format 
	if(!file_exists($dir.'/.svn') || !file_exists($updatefile)) {
		$presentation->showError('Can not read current version of the file named "'
			.$filename.'" from repository path "'.$repoFolder.'"');
	}
	$oldsize = filesize($updatefile);
	System::deleteFile($updatefile);
	$upload->processSubmit($updatefile);
	if(!file_exists($updatefile)) {
		$presentation->showError('Could not read uploaded file "'
			.$filename.'" for operation "'.getPathName($dir).'"');
	}
	// check that there is a diff compared to fromrev, should not be displayed to user: use SvnOpen
	$diff = new SvnOpen('diff');
	$diff->addArgPath($dir . $filename);	// addArgPath needs utf8 encoded argument. $updatefile has to be converted to ISO-8859-1 in toShellEncoding function in order file_exists function to work
	$diff->exec();
	if ($diff->getExitcode()) trigger_error('Could not read difference between current and uploaded file.', E_USER_WARNING);
	// can't check now, see the diff problem below // if (count($diff->getOutput())==0) trigger_error('Uploaded file is identical to the existing.', E_USER_WARNING);
	// always do update before commit
	updateAndHandleConflicts($dir, $presentation);
	// create the commit command
	$commit = new SvnEdit('commit');
	$commit->setMessage($upload->getMessage());
	$commit->addArgPath($dir);
	// locks are not set in this working copy -- reclaim
	if ($upload->isLocked()) {
		$unlock = new SvnOpen('unlock');
		$unlock->addArgUrl($upload->getTargetUrl());
		if ($unlock->exec()) trigger_error("Could not unlock the file for upload. ".implode(",",$unlock->getOutput()), E_USER_ERROR);
		if (!$upload->getUnlock()) {
			// acquire the lock again
			$lock = new SvnEdit('lock');
			$lock->addArgPath($dir . $filename);
			$lock->setMessage($upload->getLockComment());
			$lock->exec();
			$commit->addArgOption('--no-unlock');
		}
	}
	$commit->exec();
	// Seems that there is a problem with svn 1.3.0 and 1.3.1 that it does not always see the update on a replaced file
	//  remove this block when we don't need to support svn versions onlder than 1.3.2
	// Looks like this is a problem on svn 1.4.0 on OpenSuSE too.
	if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
		if ($oldsize != filesize($updatefile)) {
			exec("echo \"\" >> \"$updatefile\"");
			$commit = new SvnEdit('commit');
			$commit->setMessage($upload->getMessage());
			$commit->addArgPath($dir);
			$commit->exec();
		}
	}
	// clean up
	$upload->cleanUp();
	// remove working copy
	System::deleteFolder($dir);
	// commit returns nothing if there are no local changes
	if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
		// normal behaviour
		displayEditAndExit($presentation, null, 'The uploaded file '.$upload->getOriginalFilename()
			.' is identical to the current file '.$upload->getName());
	}
	if (!$commit->isSuccessful()) {
		displayEditAndExit($presentation, null, 'Failed to save file');
	}
	// TODO present as error if any of the operations failed (or did they already exit?)
	displayEdit($presentation, dirname($upload->getTargetUrl().'/'), 
		'New version committed',
		$upload->getTargetUrl().' is now at revision '.$commit->getCommittedRevision());
}

function _canEditAsTextarea($mimetype) {
	return $mimetype=='text/plain';
}

/**
 * Runs svn update in a working copy and reports the result to the user.
 */
function updateAndHandleConflicts($workingCopyPath, $presentation) {
	$update = new SvnEdit('update');
	$update->addArgPath($workingCopyPath);
	$update->exec('Updating to see if there is conflicts with other new changes');
	// TODO use conflicthandler to detect conflicts (exit code is still 0 on conflict)
}

/**
 * File upload, new file in a folder or new version of existing-
 * Also compatible with posted contents from a form.
 * Validation is done by the rules at the top of this script
 */
class Upload {
	var $file_id;

	/**
	 * Constructor
	 * @param formFieldName the name of the form field that contains the file
	 */
	function Upload($formFieldName) {
		$this->file_id = $formFieldName;
	}

	function processSubmit($destinationFile) {
		if (isset($_POST['usertext'])) {
			$type = isset($_POST['type']) ? $_POST['type'] : '';
			editWriteNewVersion($_POST['usertext'], $destinationFile, $type);
			return;
		}
		$current = $_FILES[$this->file_id]['tmp_name'];;
		if (move_uploaded_file($current, $destinationFile)) {
			// ok
		} else {
			trigger_error("Could not access the uploaded file ".$this->getOriginalFilename(), E_USER_ERROR);
		}
	}
	
	function cleanUp() {
		// temp file has already been moved, so no cleanup needed
	}
	
	/**
	 * @return true if this is a new file, false if it is a modification
	 */
	function isCreate() {
		return isset($_POST['create']) && $_POST['create']=='yes';
	}
	
	/**
	 * @return name given by the user, or original filename if it should not change.
	 *  Not encoded,
	 */
	function getName() {
		if ($this->isCreate()) {
			return $_POST['name'];
		}
		return getPathName(getTarget());
	}
	
	/**
	 * @return original file name on client
	 */
	function getOriginalFilename() {
		return $_FILES[$this->file_id]['name'];
	}
	
	/**
	 * @return log message
	 */
	function getMessage() {
		return $_POST['message'];
	}
	
	/**
	 * @return destination url in repository
	 */
	function getTargetUrl() {
		if ($this->isCreate()) {
			return getTargetUrl() . $this->getName();
		}
		return getTargetUrl();
	}
	
	/**
	 * @return mime type
	 */
	function getType() {
		return $_FILES[$this->file_id]['type'];
	}

	/**
	 * @return file size in bytes
	 */
	function getSize() {
		return $_FILES[$this->file_id]['size'];	
	}

	/**
	 * @return upload error code, if any
	 */
	function getErrorCode() {
		return $_FILES[$this->file_id]['error'];
	}
	
	/**
	 * @return boolean true if the user wants svn:needs-lock on the new file
	 */
	function getNeedsLock() {
		if ($this->isCreate()) {
			return $_POST['needslock'];
		} else {
			trigger_error('Needs-lock is not supported for new version.', E_USER_ERROR);
		}
	}
	
	/**
	 * @return int revision number that a new version is based on
	 */
	function getFromrev() {
		if ($this->isCreate()) {
			trigger_error('This is a new file and can not be based on a revsion', E_USER_ERROR);
		} else {
			$r = new RevisionRule('fromrev');
			return $r->getValue();
		}
	}
	
	/**
	 * @return boolean true if the file is locked by the current user
	 *  (if it is locked by someone else we should not have this upload)
	 */
	function isLocked() {
		// not working // return array_key_exists('unlock', $_POST);
		return array_key_exists('lockcomment', $_POST);
	}
	
	/**
	 * @return boolean true if the user wants to release the lock on the file at commit
	 */
	function getUnlock() {
		if (!$this->isLocked()) {
			trigger_error('Server error. Falsely assumed that the file is locked.', E_USER_ERROR);
		} else {
			return $_POST['unlock'];
		}
	}
	
	/**
	 * @return String the lock comment on the file
	 *  (make sure it should be locked before calling this method, or remove the checks)
	 */
	function getLockComment() {
		if (!$this->isLocked()) {
			trigger_error('Flow error. Currently locking requires the file to be locked before upload', E_USER_ERROR);
		} elseif ($this->getUnlock()) {
			trigger_error('Flow error. The file will be unlocked after commit, so no message is needed');
		} else {
			return $_POST['lockcomment'];
		}
	}
}
?>
