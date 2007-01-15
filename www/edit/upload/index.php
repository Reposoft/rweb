<?php
/**
 * Upload new version of file
 * @package edit
 */
require("../../conf/Presentation.class.php");
require("../SvnEdit.class.php");
require("mimetype.inc.php");
// and here's where the login_getMimeType function is currently
require("../../open/SvnOpenFile.class.php");

define('MAX_FILE_SIZE', 1024*1024*10);

// name only exists for new files, not for new version requests
new FilenameRule("name");
new NewFilenameRule("name", getTarget());

if ($_SERVER['REQUEST_METHOD']=='GET') {
	showUploadForm();
} else {
	processUpload();
}
	
function showUploadForm() {
	$template = new Presentation();
	$target = getTarget();
	$targeturl = getTargetUrl();
	$isfile = isTargetFile();
	if ($isfile) {
		$mimetype = login_getMimeType($targeturl);
		if (_canEditAsTextarea($mimetype)) {
			$template->assign('istext', true);
		} else {
			$template->assign('istext', false);
		}
		$template->assign('repository', getParent($targeturl));
	} else {
		$template->assign('repository', $targeturl);
	}
	$template->assign('maxfilesize',MAX_FILE_SIZE);
	$template->assign('isfile',$isfile);
	$template->assign('target',$target);
	if (isset($_GET['text'])) {
		$template->assign('targeturl', getTargetUrl());
		$template->display($template->getLocaleFile(dirname(__FILE__).'/index-text'));
	} else {
		$template->display();
	}
}

function processUpload() {
	$presentation = new Presentation();
	$upload = new Upload('userfile');
	if ($upload->isCreate()) {
		Validation::expect('name');
		$newfile = toPath(tempnam(rtrim(getTempDir('upload'),'/'), ''));
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
			echo('Recommending mime type '.$clientMime); exit;
		}
		// clean up
		deleteFile($newfile);
		$upload->cleanUp();
		// show results
		$edit->present($presentation, dirname($upload->getTargetUrl()));
	} else {
		$dir = getTempnamDir('upload'); // same tempdir as create, but subfolder
		$repoFolder = dirname($upload->getTargetUrl());
		// check out existing files
		$checkout = new SvnEdit('checkout');
		$checkout->addArgOption('--non-recursive');
		$checkout->addArgUrl($repoFolder);
		$checkout->addArgPath($dir);
		$checkout->exec();
		if (!$checkout->isSuccessful()) {
			$presentation->showError("Could not read current version of file "
				.$upload->getTargetUrl().". ".$checkout->getResult());
		}
		$checkout->show($presentation);
		// upload file to working copy
		$filename = $upload->getName();
		$updatefile = toPath($dir . $filename);
		if(!file_exists($dir.'/.svn') || !file_exists($updatefile)) {
			$presentation->showError('Can not read current version of the file named "'
				.$filename.'" from repository path "'.$repoFolder.'"');
		}
		$oldsize = filesize($updatefile);
		deleteFile($updatefile);
		$upload->processSubmit($updatefile);
		if(!file_exists($updatefile)) {
			$presentation->showError('Could not read uploaded file "'
				.$filename.'" for operation "'.basename($dir).'"');
		}
		// store the diff in the presentation object
		$diff = new SvnEdit('diff');
		$diff->addArgPath($updatefile);
		$diff->exec();
		$diff->showOrFail($presentation);
		//not used//$presentation->assign('diff', $diff->getResult());
		// create the commit command
		$commit = new SvnEdit('commit');
		$commit->setMessage($upload->getMessage());
		$commit->addArgPath($dir);
		//exit;
		$commit->exec();
		// Seems that there is a problem with svn 1.3.0 and 1.3.1 that it does not always see the update on a replaced file
		//  remove this block when we don't need to support svn versions onlder than 1.3.2
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
		deleteFolder($dir);
		// commit returns nothing if there are no local changes
		if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
			// normal behaviour
			presentEditAndExit($presentation, null, 'The uploaded file '.$upload->getOriginalFilename()
				.' is identical to the current file '.$upload->getName());
		}
		// show results
		$commit->show($presentation);
		// todo present error
		presentEdit($presentation, dirname($upload->getTargetUrl()), 
			'New version committed',
			$upload->getTargetUrl().' is now at revision '.$commit->getCommittedRevision());
	}
}

function _canEditAsTextarea($mimetype) {
	return $mimetype=='text/plain';
}

// validation is done by the rules at the top of this script
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
			$this->processPastedContents($destinationFile);
			return;
		}
		$current = $_FILES[$this->file_id]['tmp_name'];;
		if (move_uploaded_file($current, $destinationFile)) {
			// ok
		} else {
			trigger_error("Could not access the uploaded file ".$this->getOriginalFilename(), E_USER_ERROR);
		}
	}
	
	// handle request that is not a file upload, but the contents of a big textarea named 'userfile'
	function processPastedContents($destinationFile) {
		$contents = $_POST['usertext'];
		$fp = fopen($destinationFile, 'w+');
		if ($fp) {
			fwrite($fp, $contents);
			fclose($fp);
		} else {
			trigger_error("Couldn't write file contents from the submitted text.", E_USER_ERROR);
		}
	}
	
	function cleanUp() {
		// temp file has already been moved, so no cleanup needed
	}
	
	/**
	 * @return true if this is a new file, false if it should be created
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
		return basename(getTarget());
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
	 * @return destination url when uploaded for user's access to the file
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
}
?>
