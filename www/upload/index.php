<?php
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));
require( PARENT_DIR."/conf/repos.properties.php" );
require( PARENT_DIR."/conf/language.inc.php" );
require( PARENT_DIR."/smarty/smarty.inc.php" );

require( PARENT_DIR."/edit/edit.class.php" );

if ($_SERVER['REQUEST_METHOD']=='GET') {
	$smarty= getTemplateEngine();
	$smarty->assign('path',getPath());
	$smarty->assign('file',getFile());
	$smarty->assign('targeturl',getTargetUrl());
	$smarty->display(DIR . getLocaleFile());
} else {
	$upload = new Upload();
	$upload->processSubmit();
	if ($upload->isCreate()) {
		$edit = new Edit('import');
		$edit->addArgument($upload->getFilepath());
		$edit->addArgument($upload->getTargeturl());	
	} else {
		echo("Upload of new version not supported yet");exit;
	}
	$edit->setMessage($upload->getMessage());
	// execute command
	$edit->execute();
	// clean up
	$upload->cleanUp();
	// show results
	$edit->present(getTemplateEngine(), $_POST['targeturl']);
}

class Upload {
	var $filepath; // temp file location

	function processSubmit() {
		$current = $this->getFilepath();
		$tmpFile = tempnam(getTempDir('upload'), '');
		if (move_uploaded_file($current, $tmpFile)) {
			$this->filepath = $tmpFile;
		} else {
			echo("Could not access the uploaded file. Possible file upload attack!\n");exit;
		}
	}
	
	function cleanUp() {
		unlink($this->getFilepath());
	}
	
	/**
	 * @return name given by the user, or original filename if it should not change
	 *  not encoded
	 */
	function getName() {
		if (isset($_POST['name'])) {
			return $_POST['name'];
		}
		return $this->getFilename();
	}
	
	/**
	 * @return true if this is a new file, false if it should be created
	 */
	function isCreate() {
		return isset($_POST['create']) && $_POST['create']=='yes';
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
	 * @return current location of uploaded file, absolute path
	 */
	function getFilepath() {
		if (isset($this->filepath)) {
			return $this->filepath;
		}
		return $_FILES['userfile']['tmp_name'];
	}
	
	/**
	 * @return original file name on client
	 */
	function getFilename() {
		return $_FILES['userfile']['name'];
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
