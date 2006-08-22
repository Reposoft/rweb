<?php
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));
require( PARENT_DIR."/conf/language.inc.php" );
require( PARENT_DIR."/smarty/smarty.inc.php" );

require( PARENT_DIR."/eidt/edit.class.php" );

if ($_SERVER['REQUEST_METHOD']=='GET') {
	$smarty= getTemplateEngine();
	$smarty->assing('path',dirname(getTarget()));
	$smarty->assign('file',basename(getTarget()));
	$smarty->assign('targeturl',getTargetUrl());
	$smarty->display(DIR . getLocaleFile());
} else {
	$upload = new Upload();
	$upload->processSubmit();
	$edit = new Edit('import');
	$edit->setMessage($upload->getMessage());
	$edit->addArgument($upload->getFile());
	$edit->addArgument(getUploadTargetUrl($upload));
}

// return the repository url for the new or updated file
function getUploadTargetUrl($upload) {
	
}

class Upload {
	function processSubmit() {
		// do the file move recommended in the php manual
	}
	
	/**
	 * @return name given by the user, or original filename if it should not change
	 */
	function getName() {
		if (isset($_POST['name'])) {
			return $_POST['name'];
		}
		return getFilename();
	}
	
	/**
	 * @return log message
	 */
	function getComment() {
		return $_POST['comment'];
	}

	/**
	 * @return current location of uploaded file, absolute path
	 */
	function getFile() {
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
