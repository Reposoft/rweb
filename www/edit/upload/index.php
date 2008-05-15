<?php
/**
 * Upload new version of file
 * @package edit
 */
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');
require(dirname(dirname(__FILE__)).'/SvnEdit.class.php');
//not 1.1//require("mimetype.inc.php");
require(dirname(__FILE__).'/filewrite.inc.php');

// the only use for the numeric value is the MAX_FILE_SIZE parameter in the form, which is good for what?
// without it we could just append 'b' to the ini value
$maxsize = ini_get('upload_max_filesize');
if (($p = strpos($maxsize,'M'))) $maxsize = substr($maxsize, 0, $p) * 1024 * 1024;
define('MAX_FILE_SIZE',$maxsize); // now we haven't checked max post size, we assume it is high enough

// need prolonged script execution time for big uploads, seconds
define('UPLOAD_MAX_TIME', 10*60);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	set_time_limit(UPLOAD_MAX_TIME);
}

// name only exists for new files, not for new version requests
new FilenameRule("name");
// disabled as plain uploads don't care about type //new EditTypeRule('type');

if (!isTargetSet()) {
	trigger_error("Could not upload file. The time limit of 10 minutes may have been exceeded," 
		." or the file is larger than ".formatSize(MAX_FILE_SIZE).".", E_USER_ERROR);
	// TODO: is this extra protection needed? Code should not contain exits, as they are not testable.
	exit;
} else {
	$folderRule = new ResourceExistsRule('target');
	new NewFilenameRule("name", $folderRule->getValue());
	
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
 * Reads a summary of the svn log
 * @return array[int revision => array]
 */
function getLog($targetUrl) {
	$limit = 10;
	$svnlog = new SvnOpen('log');
	$svnlog->addArgOption('-q');
	$svnlog->addArgOption('--stop-on-copy'); // based-on-revision can't handle renamed files
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
	header("Cache-Control: no-cache, must-revalidate"); // the history part of the form must be updated after successful upload to avoid strange conflicts - disable caching until we have a better solution
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
	} else {
		$template->assign('download', false);
	}
	$template->display();
}

/**
 * @param Upload $upload
 */
function processNewFile($upload) {
	Validation::expect('name', 'type');
	$presentation = Presentation::background();
	$newfile = System::getTempFile('upload', $upload->getName());
	$upload->processSubmit($newfile);
	$edit = new SvnEdit('import');
	$edit->addArgPath($newfile);
	$edit->addArgUrl($upload->getTargeturl());
	$edit->setMessage($upload->getMessage());
	$edit->exec();
	// In 1.1 we don't customize mime types, but here's how to get the type from the browser
	// If the customer wants mime types to be set, it can be configured with svn autoprops
	// Currently we rely on autoprops to set text/html for html pages edited online
	//$clientMime = $upload->getType();
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
	$presentation = Presentation::background();
	$dir = System::getTempFolder('upload');
	$repoFolder = getParent($upload->getTargetUrl());
	// check out existing files of the given revision
	$filename = $upload->getName();
	$fromrev = $upload->getFromrev();
	// try svn 1.5 sparse checkout, with fallback to non-recursive complete checkout
	$checkout = new SvnEdit('checkout');
	$checkout->addArgOption('--depth', 'empty', false);
	if ($fromrev) $checkout->addArgOption('-r', $fromrev, false);
	$checkout->addArgUrl($repoFolder);
	$checkout->addArgPath($dir);
	$checkout->execNoDisplayOnError();
	if ($checkout->isSuccessful()) {
		// fetch only the file we're editing
		$sparse = new SvnEdit('update');
		if ($fromrev) $sparse->addArgOption('-r', $fromrev, false); // repeat the revision number from sparse checkout
		$sparse->addArgPath($dir . $filename);
		if ($sparse->execNoDisplay()) trigger_error('Failed to get target file from repository.', E_USER_ERROR);
	} else {
		// fallback: svn 1.4 and older
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
	}
	// for PHP operations like file_exist below, we need to use an encoded path on windows
	// while the subversion commands use $dir.$filename and the setlocale in Command class
	$updatefile = toPath($dir . $filename);
	// upload file to working copy
	if(!file_exists($dir.'/.svn') || !file_exists($updatefile)) {
		$presentation->showError('Can not read current version of the file named "'
			.$filename.'" from repository path "'.$repoFolder.'"');
	}
	// check for versioned symbolic links, so we don't overwrite local file.
	// with sparse checkout for svn 1.5 this solves the security issue with versioned symlinks
	if (is_link($updatefile)) {
		$presentation->showError('The file "'.$filename.'" is a symbolic link (svn:special).'
			.' It can not be overwritten with uploaded contents.');
	}
	// delete current working copy file and get the uploaded file instead
	System::deleteFile($updatefile);
	$upload->processSubmit($updatefile);
	if(!file_exists($updatefile)) {
		$presentation->showError('Could not read uploaded file "'
			.$filename.'" for operation "'.getPathName($dir).'"');
	}
	// check that there is a diff compared to fromrev, should not be displayed to user: use SvnOpen
	$diff = new SvnOpen('diff');
	// repeat: addArgPath needs utf8 encoded argument, $updatefile is used for file_exists etc which might need ISO-8859-1 (toShellEncoding)
	$diff->addArgPath($dir . $filename);
	if ($diff->exec()) trigger_error('Could not read difference between current and uploaded file.', E_USER_ERROR);
	// can not do a validation rule on multipart POST
	if (count($diff->getOutput())==0) Validation::error('The uploaded file is identical to the latest version in repository.', E_USER_WARNING);
	// always do update before commit
	updateAndHandleConflicts($dir, $presentation);
	// create the commit command
	$commit = new SvnEdit('commit');
	$commit->setMessage($upload->getMessage());
	$commit->addArgPath($dir . $filename); // only commit the file
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
	$update->addArgOption('--non-recursive');
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
		$current = $_FILES[$this->file_id]['tmp_name'];
		if (is_uploaded_file($current)) {
			$from = fopen($current, 'rb');
			$to = fopen($destinationFile, 'wb');
			while (!feof($from)) {
				$contents = fread($from, 8192);
				fwrite($to, $contents);
			}
			fclose($to);
			fclose($from);
			unlink($current); // because we don't use move_uploaded_file anymore
		} else {
			if ($n = $this->getOriginalFilename()) trigger_error("Could not access the uploaded file ", E_USER_ERROR);
			Validation::error('A local file must be selected for upload');
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
	 * Reads the MIME value set by the browser in multipart form.
	 * @return String mime type 
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
