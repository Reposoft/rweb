<?php
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));

require( PARENT_DIR."/conf/Presentation.class.php" );
require( PARENT_DIR."/edit/edit.class.php" );

define('MAX_FILE_SIZE', 1000000);

if ($_SERVER['REQUEST_METHOD']=='GET') {
	if (isset($_GET['result'])) {
		header('Location: ' . str_replace("/upload/", "/edit/", getSelfUrlAndQuery()));
		exit;
	}
	$template= new Presentation();
	$template->assign('maxfilesize',MAX_FILE_SIZE);
	$template->assign('path',getPath());
	$template->assign('file',getFile());
	$template->assign('targeturl',getTargetUrl());
	$template->display();
} else {
	$presentation = new Presentation();
	$upload = new Upload();
	if ($upload->isCreate()) {
		$upload->processSubmit();
		$edit = new Edit('import');
		$edit->addArgPath($upload->getFilepath());
		$edit->addArgUrl($upload->getTargeturl());
		$edit->setMessage($upload->getMessage());
		$edit->execute();
		// clean up
		$upload->cleanUp();
		// show results
		$edit->present($presentation, dirname($upload->getTargetUrl()));
	} else {
		$dir = getTempnamDir('upload'); // same tempdir as in Upload's default filepath
		$repoFolder = dirname($upload->getTargetUrl());
		// check out existing files
		$checkout = new Edit('checkout');
		$checkout->addArgOption('--non-recursive');
		$checkout->addArgUrl($repoFolder);
		$checkout->addArgPath($dir);
		$checkout->execute();
		if (!$checkout->isSuccessful()) {
			$presentation->trigger_error("Could not read current version of file "
				.$upload->getTargetUrl().". ".$checkout->getResult());
		}
		// upload file to working copy
		$filename = $upload->getName();
		$file = $dir . '/' . $filename;
		if(!file_exists($dir.'/.svn') || !file_exists($file)) {
			$presentation->trigger_error('Can not read current version of the file named "'
				.$filename.'" from repository path "'.$repoFolder.'"');
		}
		unlink($file);
		$upload->setFilepath($file);
		$upload->processSubmit();
		if(!file_exists($file)) {
			$presentation->trigger_error('Could not read uploaded file "'
				.$filename.'" for operation "'.basename($dir).'"');
		}
		// create the commit commant
		$commit = new Edit('commit');
		$commit->setMessage($upload->getMessage());
		$commit->addArgPath($dir);
		$commit->execute();
		// clean up
		$upload->cleanUp();
		// remove working copy
		rmdir($dir); // TODO do recursively
		// commit returns nothing if there are no local changes
		if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
			$presentation->trigger_error('The uploaded file '.$upload->getOriginalFilename()
				.' is identical to the current file '.$upload->getName());
		}
		// show results
		$commit->present($presentation, dirname($upload->getTargetUrl()));
	}
}

// TODO check allowed characters before file operations are performed
// (in the getters probably, because they might be used before processSubmit)
class Upload {
	var $filepath; // temp file location

	/**
	 * Set location where uploaded file should be placed
	 */
	function setFilepath($absolutePath) {
		$this->filepath = $absolutePath;
	}
	
	/**
	 * @return current location of uploaded file, absolute path
	 */
	function getFilepath() {
		if (isset($this->filepath)) {
			return $this->filepath;
		}
		$this->filepath = tempnam(getTempDir('upload'), '');
		return $this->filepath;
	}

	function processSubmit() {
		$current = $_FILES['userfile']['tmp_name'];;
		if (move_uploaded_file($current, $this->getFilepath())) {
			// ok
		} else {
			trigger_error("Could not access the uploaded file ".$this->getOriginalFilename());exit;
		}
	}
	
	function cleanUp() {
		unlink($this->getFilepath());
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
		return $_POST['name'];
	}
	
	/**
	 * @return original file name on client
	 */
	function getOriginalFilename() {
		return $_FILES['userfile']['name'];
	}
	
	/**
	 * @return log message
	 */
	function getMessage() {
		return $_POST['message'];
	}
	
	/**
	 * @return destination url for accessing the uploaded file
	 */
	function getTargetUrl() {
		if ($this->isCreate()) {
			return $_POST['targeturl'] . rawurlencode($this->getName());
		}
		return $_POST['targeturl'];
	}
	
	/**
	 * @return mime type
	 */
	function getType() {
		return $_FILES['userfile']['type'];
	}

	/**
	 * @return file size in bytes
	 */
	function getSize() {
		return $_FILES['userfile']['size'];	
	}

	/**
	 * @return upload error code, if any
	 */
	function getErrorCode() {
		return $_FILES['userfile']['error'];
	}
}
?>
