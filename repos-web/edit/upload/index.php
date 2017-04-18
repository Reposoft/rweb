<?php
/**
 * Upload new version of file
 * @package edit
 */
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');
require(dirname(dirname(__FILE__)).'/SvnEdit.class.php');
require(dirname(dirname(dirname(__FILE__))).'/open/getlog.php');
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

// prefix for query params and form fields to be treated as svn properties
define('UPLOAD_PROP_PREFIX', 'prop_');

// name only exists for new files, not for new version requests
new FilenameRule("name");
// disabled as plain uploads don't care about type //new EditTypeRule('type');

if (!isTargetSet()) {
	trigger_error("Could not upload file. The time limit of 10 minutes may have been exceeded," 
		." or the file is larger than ".formatSize(MAX_FILE_SIZE).".", E_USER_ERROR);
	// TODO: is this extra protection needed? Code should not contain exits, as they are not testable.
	exit;
} else {

	// Safari 4 does not resend credentials for upload even if prompted at form GET
	if (strContains($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit') && $_SERVER['REQUEST_METHOD'] == 'POST') {
		targetLogin();
	}
	
	if (isset($_POST['usertext'])) { // A plain POST, not multipart upload.
		// - browser might come from edit/text/ and thus
		// it might not automatically send credentials for this path
		targetLogin();	
	}
	
	$folderRule = new ResourceExistsRule('target');
	new NewFilenameRule("name", $folderRule->getValue());
	
	if ($_SERVER['REQUEST_METHOD'] != 'POST') {
		// Need to make sure client is authenticated before upload,
		// because a retry after file upload would be irritating if the file is big
		targetLogin();
		showUploadForm();
	} else {
		$upload = new Upload('userfile');
		processFile($upload);
	}
}
	
function showUploadForm() {
	header("Cache-Control: no-cache, must-revalidate"); // the history part of the form must be updated after successful upload to avoid strange conflicts - disable caching until we have a better solution
	$template = Presentation::getInstance();
	$target = getTarget();
	// if target is a file then this is upload new version
	$file = new SvnOpenFile($target);
	if (!$file->isWritable()) {
		trigger_error('Write access denied', E_USER_NOTICE);
	}
	if ($file->isFile()) {
		$template->assign_by_ref('file', $file);
		$log = getLog($file->getUrl());
		$template->assign_by_ref('log', $log);
		$template->assign('folderurl', getParent($file->getUrl()));
	} else {
		$template->assign('folderurl', $file->getUrl());
		$template->assign('suggestname', isset($_GET['suggestname']) ? $_GET['suggestname'] : '');
	}
	$template->assign('maxfilesize',MAX_FILE_SIZE);
	$template->assign('isfile',$file->isFile());
	$template->assign('target',$target);
	foreach ($_GET as $param => $value) {
		$custom = array();
		if (strBegins($param, UPLOAD_PROP_PREFIX)) {
			$custom[$param] = $value;
		}
		// custom should be selected so they can not overwrite other fields
		$template->assign('customfields', $custom);
	}
	if (isset($_GET['download'])) {
		$revParam = isset($_REQUEST['rev']) ? '&rev='.$_REQUEST['rev'] : '';
		// does not use getRepository so "base" must be added manually
		$baseParam = isset($_REQUEST['base']) ? '&base='.$_REQUEST['base'] : '';
		// absolute url that works in meta refresh
		$template->assign('download', getWebapp().'open/download/?target='.urlencode(getTarget()).$revParam.$baseParam);
	} else {
		$template->assign('download', false);
	}
	$template->display();
}

/**
 * 
 * @param {Upload} $upload The file upload handler
 */
function processFile($upload) {
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
	$checkout->addArgUrlPeg($repoFolder, $fromrev); // peg rev to avoid unexpected results if the given rev does not exist
	$checkout->addArgPath($dir);
	$checkout->execNoDisplayOnError();
	if ($checkout->isSuccessful()) {
		// fetch only the file we're editing
		$sparse = new SvnEdit('update');
		if ($fromrev) $sparse->addArgOption('-r', $fromrev, false); // repeat the revision number from sparse checkout
		$sparse->addArgPath($dir . $filename);
		if ($sparse->execNoDisplay()) trigger_error('Failed to get target file from repository.', E_USER_ERROR);
	} else {
		trigger_error('Failed to create working copy', E_USER_ERROR);
	}
	// for PHP operations like file_exist below, we need to use an encoded path on windows
	// while the subversion commands use $dir.$filename and the setlocale in Command class
	$updatefile = toPath($dir . $filename);
	// upload file to working copy
	if(!file_exists($dir.'/.svn') || !$upload->isCreate() && !file_exists($updatefile)) {
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
	if (!$upload->isCreate()) {
		System::deleteFile($updatefile);
	}
	$upload->processSubmit($updatefile);
	if(!file_exists($updatefile)) {
		$presentation->showError('Could not read uploaded file "'
			.$filename.'" for operation "'.getPathName($dir).'"');
	}
	if ($upload->isCreate()) {
		$add = new SvnOpen('add');
		$add->addArgPath($dir . $filename);
		$add->exec();
	}
	// check that there is a diff compared to fromrev, should not be displayed to user: use SvnOpen
	$diff = new SvnOpen('diff');
	// repeat: addArgPath needs utf8 encoded argument, $updatefile is used for file_exists etc which might need ISO-8859-1 (toShellEncoding)
	$diff->addArgPath($dir . $filename);
	if ($diff->exec()) trigger_error('Could not read difference between current and uploaded file.', E_USER_ERROR);
	// can not do a validation rule on multipart POST
	if (count($diff->getOutput())==0) Validation::error('The uploaded file is identical to the latest version in repository.', E_USER_WARNING);
	// allow custom forms to set properties at file creation and modification
	setArbitraryProperties($dir . $filename, $upload);
	// always do update before commit
	updateAndHandleConflicts($dir, $presentation);
	// create the commit command
	$commit = new SvnEdit('commit');
	$commit->setMessage($upload->getMessage());
	$commit->addArgPath($dir . $filename); // only commit the file
	// The form/request may order unlock, without this set we won't even steal the same user's own lock
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
	$commit->addArgRevpropsFromPost();
	$commit->exec();
	// clean up
	$upload->cleanUp();
	// remove working copy
	System::deleteFolder($dir);
	// commit returns nothing if there are no local changes
	if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
		// normal behaviour
		displayEditAndExit($presentation, null, null, 'The uploaded file '.$upload->getOriginalFilename()
			.' is identical to the current file '.$upload->getName());
	}
	if (!$commit->isSuccessful()) {
		displayEditAndExit($presentation, null, null, 'Failed to save file');
	}
	// TODO present as error if any of the operations failed (or did they already exit?)
	displayEdit($presentation, getParent($upload->getTargetUrl()),
		$upload->getTarget(),
		$upload->isCreate() ? 'File added' : 'New version committed',
		$upload->getTargetUrl().' is at revision '.$commit->getCommittedRevision());
}

function _canEditAsTextarea($mimetype) {
	return $mimetype=='text/plain';
}

function setArbitraryProperties($workingCopyFilePath, $upload) {
	$props = $upload->getCustomProperties();
	foreach ($props as $name => $value) {
		$propset = new SvnEdit('propset');
		if ($value == "*") {
			$argtempfile = System::getTempFile();
			$fp = fopen($argtempfile, 'w');
			fwrite($fp, $value);
			fclose($fp);
			$propset->addArgOption("$name --file", $argtempfile);
		} else {
			$propset->addArgOption($name, $value);
		}
		$propset->addArgPath($workingCopyFilePath);
		$propset->exec("Set property '$name' to '$value'");
	}
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
		Validation::expect('type'); // there's custom writing to file from form input
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
			if (!isReposJava()) { // removes uploaded tmp automatically
				unlink($current); // because we don't use move_uploaded_file anymore
			}
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
			Validation::expect('name');
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
	 * @return destination path
	 */
	function getTarget() {
		if ($this->isCreate()) {
			return getTarget() . $this->getName();
		}
		return getTarget();
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
			return 'HEAD';
		} else {
			Validation::expect('fromrev'); // old behavior, we could probably have a default
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
	
	/**
	 * Duplicated to ../copy/index.php so if more operations need prop support we should extrac to reusable location.
	 * @return {array(String)} arbitrary propery name to values 
	 */
	function getCustomProperties() {
		$props = array();
		foreach ($_POST as $name => $value) {
			if (strBegins($name, UPLOAD_PROP_PREFIX)) {
				$props[substr($name, 5)] = $value;
			}
		}
		return $props;
	}
}
?>
