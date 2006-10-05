<?php
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));

require( PARENT_DIR."/conf/Presentation.class.php" );
require( PARENT_DIR."/edit/edit.class.php" );

define('MAX_FILE_SIZE', 1024*1024*10);

// name only exists for new files, not for new version requests
new FilenameRule("name");
new NewFilenameRule("name");

if ($_SERVER['REQUEST_METHOD']=='GET') {
	$template = new Presentation();
	$target = getTarget();
	$isfile = isTargetFile();
	if ($isfile) {
		$mimetype = login_getMimeType($targeturl);
		if ($mimetype && strpos($mimetype, 'application/') == 0) {
			$template->assign('isbinary', true);
		}
		$template->assign('repository', getParent(getTargetUrl()));
	} else {
		$template->assign('repository', getTargetUrl());
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
} else {
	$presentation = new Presentation();
	$upload = new Upload('userfile');
	if ($upload->isCreate()) {
		Validation::expect('name');
		$newfile = toPath(tempnam(rtrim(getTempDir('upload'),'/'), ''));
		$upload->processSubmit($newfile);
		$edit = new Edit('import');
		$edit->addArgPath($newfile);
		$edit->addArgUrl($upload->getTargeturl());
		$edit->setMessage($upload->getMessage());
		$edit->execute();
		// clean up
		deleteFile($newfile);
		$upload->cleanUp();
		// show results
		$edit->present($presentation, dirname($upload->getTargetUrl()));
	} else {
		$dir = getTempnamDir('upload'); // same tempdir as create, but subfolder
		$repoFolder = dirname($upload->getTargetUrl());
		// check out existing files
		$checkout = new Edit('checkout');
		$checkout->addArgOption('--non-recursive');
		$checkout->addArgUrl($repoFolder);
		$checkout->addArgPath($dir);
		$checkout->execute();
		if (!$checkout->isSuccessful()) {
			$presentation->trigger_error("Could not read current version of file "
				.$upload->getTargetUrl().". ".$checkout->getResult(), E_USER_WARNING);
		}
		// upload file to working copy
		$filename = $upload->getName();
		$updatefile = $dir . '/' . $filename;
		if(!file_exists($dir.'/.svn') || !file_exists($updatefile)) {
			$presentation->trigger_error('Can not read current version of the file named "'
				.$filename.'" from repository path "'.$repoFolder.'"', E_USER_WARNING);
		}
		$oldsize = filesize($updatefile);
		deleteFile($updatefile);
		$upload->processSubmit($updatefile);
		if(!file_exists($updatefile)) {
			$presentation->trigger_error('Could not read uploaded file "'
				.$filename.'" for operation "'.basename($dir).'"', E_USER_WARNING);
		}
		// store the diff in the presentation object
		$diff = new Edit('diff');
		$diff->addArgPath($updatefile);
		$diff->execute();
		$presentation->assign('diff', $diff->getResult());
		// create the commit command
		$commit = new Edit('commit');
		$commit->setMessage($upload->getMessage());
		$commit->addArgPath($dir);
		//exit;
		$commit->execute();
		// Seems that there is a problem with svn 1.3.0 and 1.3.1 that it does not always see the update on a replaced file
		//  remove this block when we don't need to support svn versions onlder than 1.3.2
		if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
			if ($oldsize != filesize($updatefile)) {
				exec("echo \"\" >> \"$updatefile\"");
				$commit = new Edit('commit');
				$commit->setMessage($upload->getMessage());
				$commit->addArgPath($dir);
				$commit->execute();
			}
		}
		// clean up
		$upload->cleanUp();
		// remove working copy
		deleteFolder($dir);
		// commit returns nothing if there are no local changes
		if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
			// normal behaviour
			$presentation->trigger_error('The uploaded file '.$upload->getOriginalFilename()
				.' is identical to the current file '.$upload->getName());
		}
		// show results
		$commit->present($presentation, dirname($upload->getTargetUrl()));
	}
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
			trigger_error("Could write file contents from the submitted text.", E_USER_ERROR);
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
	 * @return name given by the user, or original filename if it should not change
	 *  not encoded
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
