<?php
/**
 *
 *
 * @package open
 */
if (!class_exists('SvnOpen')) require(dirname(__FILE__).'/SvnOpen.class.php');

/**
 * Returns the mime type for a file in the repository.
 * If revision is HEAD (which it is when the second argument is omited)
 * the mime type is read from the HTTP header of the repository resource. That gives us defaults
 * based on filename extension, without the need to maintain our ow list.
 * If revision number is not head, login_getMimeTypeProperty is used.
 * @param targetUrl the file
 * @param revision, optional revision number, if not HEAD
 * @return the mime type string, or false if unknown (suggesting application/x-unknown)
 * @deprecated use SvnOpenFile class instead
 */
function login_getMimeType($targetUrl, $revision=HEAD) {
	if ($revision!=HEAD) {
		return login_getMimeTypeProperty($targetUrl, $revision);
	}
	$headers = getHttpHeaders($targetUrl, getReposUser(), _getReposPass());
	if (!isset($headers['Content-Type'])) trigger_error("Could not get content type for target $targetUrl");
	$c = $headers['Content-Type'];
	if (strContains($c, ';')) return substr($c, 0, strpos($c, ';'));
	return $c;
}

/**
 * Returns the value of the svn:mime-type property of a file with revision number.
 *
 * @param String $targetUrl the file url
 * @param String $revision the revision number, integer or HEAD
 * @return String mime type, or false if property not set.
 * @deprecated use SvnOpenFile class instead
 */
function login_getMimeTypeProperty($targetUrl, $revision) {
	$url = $targetUrl.'@'.$revision;
	$cmd = 'propget svn:mime-type '.escapeArgument($url);
	$result = login_svnRun($cmd);
	if (array_pop($result)) trigger_error("Could not find the file '$targetUrl' revision $revision in the repository.", E_USER_ERROR );
	if (count($result) == 0) { // mime type property not set, return default
		return false;
	}
	return $result[0];
}

/**
 * Reads a file directly from the repository, without the need for local temp storage.
 * 
 * First does "svn info" to check that the file exists.
 * That tells you if it is readonly or if it is locked.
 * 
 * When getContents or a send* method is called, an "svn cat" is executed.
 * To use only the file information without actually reading the file,
 * instantiate the class but don't call any of these methods.
 */
class SvnOpenFile {
	
	/**
	 * The absolute path from repository root
	 * @var String
	 */
	var $path;
	/**
	 * The complete URL
	 * @var String
	 */
	var $url;
	/**
	 * The revision number (if this is not set, number has not been identified from the revision string yet)
	 * @var int
	 */
	var $revision;
	
	/**
	 * Tries to access the file and saves the info but does not read the contents yet.
	 *
	 * @param String $path file path, absolute from repository root
	 * @param String $revision interger revision number or string revision range, svn syntax
	 * @return SvnOpenFile
	 */
	function SvnOpenFile($path, $revision=HEAD) {
		$this->path = $path;
		$this->url = getRepository().$path;
		
		if ($revision==HEAD) {
			$r = $this->_readInfoHttp();
		} else {
			$r = $this->_readInfoSvn($revision);
		}
		if (!$r) trigger_error("Could not read file information for '$path' revision $revision in repository ".getRepository(), E_USER_ERROR);
	}
	
	function getContentType() {
		// 1: If HEAD, simply get the headers from apache
		// 2: If revision != head, get the svn:mime-type property
		// 3: If revision != head, guess the mime type for relevant (common) extensions, use default if not
		// never look at file contents, too comlicated and we don't want to require fileinfo extension
	}
	
	function getContentLength() {
		// svn info
	}

	/**
	 * @return boolean true if this file is the HEAD
	 */
	function isLatestRevision() {
		
	}
	
	/**
	 * @return int the revision number. if revision is HEAD, this is the newest revision number.
	 */
	function getRevisionNumber() {
		// svn info
	}
	
	function getLastModified() {
		// svn info
	}
	
	/**
	 * Note that read-only is not a versioned property (it is caused by apache configuration).
	 * For all revisions that are not HEAD, this method has to return 'true'.
	 * To detect read-only for files, inspect "svn info --xml" and check if commit autor and date are missing.
	 * To detect read-only for folders, we need to try a webdav operation like PUT.
	 */
	function isReadOnly() {
		// svn info
	}
	
	// should we really have all that lock stuff here?
	// yes probably, since it is in svn invo
	
	function isLocked() {
		// svn info
	}
	
	/**
	 * Reads the contents of the file to... hmmm... might be binary
	 *
	 */
	function getContents() {
		
	}
	
	/**
	 * Reads the contents of the file to a string array, one item per line, without newlines.
	 *
	 */
	function getContentsText() {
		
	}
	
	/**
	 * Writes this file to the browser, assuming that no other output is sent.
	 * Used to show file to the user, or for example to use an image in an img tag.
	 */
	function sendResponse() {
		
	}
	
	/**
	 * Sends this file with a save-as box (attachment header) and content size.
	 *
	 */
	function sendAttachment() {
		
	}
	
	/**
	 * Sends this file without headers, for embedding into page.
	 * htmlescape?
	 */
	function sendInline() {
		// where do we get the current content type header? just assume that it is correct? throw error if not correct?
	}
	
	/**
	 * The equivalent of an HTTP status code when accessing this file.
	 * 200 - access ok
	 * 401 - auth required
	 * 500 - error
	 * and so on
	 * @return int status code
	 */
	function getStatus() {
		
	}
	
	/**
	 * Reads all file information with a 
	 *
	 * @param unknown_type $revision
	 * @return unknown
	 */
	function _readInfoSvn($revision) {
		$info = new SvnOpen('info', true);
		$info->addArgRevision($revision);
		$info->addArgUrl($url);
		$info->exec();
		print_r($info->getOutput());
		return true;
	}
	
	function _readInfoHttp() {
		// http can not read the actual revision number for HEAD
		
		
		return true;
	}
	
	function _readActualRevisionNumber($revision=HEAD) {
		
	}
	
}

?>
