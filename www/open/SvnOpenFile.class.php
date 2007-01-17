<?php
/**
 * @package open
 * @version $Id$
 */
if (!class_exists('SvnOpen')) require(dirname(__FILE__).'/SvnOpen.class.php');
if (!class_exists('ServiceRequest')) require(dirname(__FILE__).'/ServiceRequest.class.php');

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
	$cmd = new SvnOpen('propget');
	$cmd->addArgOption('svn:mime-type');
	$cmd->addArgUrlPeg($targetUrl, $revision);
	$cmd->exec();
	if ($cmd->getExitcode()) trigger_error("Could not find the file '$targetUrl' revision $revision in the repository.", E_USER_ERROR );
	$result = $cmd->getOutput();
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
	 * The absolute path from repository root.
	 * @var String
	 */
	var $path;
	/**
	 * The complete URL.
	 * @var String
	 */
	var $url;
	/**
	 * The revision that will be read, as given to the constructor.
	 * @var String|int HEAD or an integer revision number
	 */
	var $_revision;
	/**
	 * The array of metadata, if read.
	 * @var array[String]
	 */
	var $file = null;
	/**
	 * Headers of the url in repository HEAD.
	 * @var array[String] header name (without ':') => trimmed value
	 */
	var $head = null;
	/**
	 * Status for lat http call to url.
	 * @var int Status code, or 0 if no call has been made.
	 */
	var $headStatus = 0;
	
	/**
	 * Sets the vital information: path, url and revision string.
	 * Actual calls for data are being made (and cached) when requested with get* and is* methods.
	 *
	 * @param String $path file path, absolute from repository root
	 * @param String $revision interger revision number or string revision range, svn syntax
	 * @return SvnOpenFile
	 */
	function SvnOpenFile($path, $revision=HEAD) {
		if ($revision == null) $revision = HEAD; // allow value directly from RevisionRule->getValue
		$this->path = $path;
		$this->url = SvnOpenFile::getRepository().$path;
		$this->_revision = $revision;
	}
	
	/**
	 * Called first in every method that requires metadata
	 */
	function _read() {
		if (!is_null($this->file)) return;
		$this->file = $this->_readInfoSvn();
		if (is_null($this->file)) trigger_error("Could not read file information for '$path' revision $revision in repository ".getRepository(), E_USER_ERROR);
	}
	
	/**
	 * Called first in every method that needs the current HTTP headers of the file's URL
	 */
	function _head() {
		if ($this->headStatus > 0) return;
		$s = new ServiceRequest($this->url);
		$s->setSkipBody();
		$s->exec();
		$this->headStatus = $s->getStatus();
		$this->head = $s->getResponseHeaders();
		if ($this->headStatus == 0) trigger_error("Could not access repository using url ".$this->url.". Might be a temporary error.", E_USER_ERROR);
	}
	
	/**
	 * @return the repository root url without trailing slash
	 * @static
	 */
	function getRepository() {
		return getRepository();
	}
	
	/**
	 * @return String the username of the account used to access the file
	 */
	function getAuthenticatedUser() {
		return SvnOpen::getAuthenticatedUser();
	}
	
	function getFilename() {
		return basename($this->path);
	}
	
	function getPath() {
		return $this->path;
	}
	
	function getFolderPath() {
		return getParent($this->path);
	}
	
	function getUrl() {
		return $this->url;
	}
	
	function getFolderUrl() {
		return $this->getRepository().$this->getFolderPath();
	}

	/**
	 * Try to figure out if the revision is the latest,
	 * which is only trivial if revision was given as HEAD.
	 * @return boolean true if this file is the newest revision
	 */
	function isLatestRevision() {
		if ($this->_revision==HEAD) return true;
		// need to check the current response code
		$this->_head();
		$r = $this->_getHeadRevisionFromETag();
		if ($r !== false) {
			if ($r < $this->_revision) trigger_error("Invalid revision number $this->_revision, higher than last commit but not HEAD");
			return $r == $this->_revision;
		}
		// it is unlikely that we come this far
		trigger_error("Could not read revision number of the latest version from repository.", E_USER_ERROR);
		// TODO call _file then return false if sizes don't match
		// TODO final: either parse dates and compare or do an svn list call on HEAD and compare revisions
	}
	
	
	/**
	 * Note that read-only is not a versioned property (it is caused by apache configuration).
	 * For all revisions that are _not_ HEAD, this method has to return 'false'.
	 * To detect read-only for files or folders, it is also possible to 
	 *  inspect "svn info --xml [parent folder]" and check if commit autor and date are missing.
	 */	
	function isWritable() {
		if (!$this->isLatestRevision()) return false;
		// TODO what method should be used?
		// curl -I -u test:test -X PROPPATCH http://localhost/testrepo/demoproject/trunk/readonly/
		$r = new ServiceRequest($this->getUrl());
		$r->setCustomHttpMethod('PROPPATCH');
		$r->exec();
		return ($r->getStatus() != 403); // 400 if user has write access, so no modifications made
	}
	
	/**
	 * Checks if the file still exists, and the user still has read access to it.
	 * Note that this is not a "peg revision" check, so it might be a different file at the same URL.
	 */
	function isReadableInHead() {
		if ($this->isLatestRevision()) return true;
		$this->_head();
		return ($this->headStatus == 200);
	}
	
	/**
	 * Subversion usually puts the revision number in the ETag header.
	 * @return the revision number if found, boolean false if not
	 *  (if revision number can be 0, use ===)
	 */
	function _getHeadRevisionFromETag() {
		$this->_head();
		if (!array_key_exists('ETag', $this->head)) return false;
		$etag = $this->head['ETag'];
		$pattern = '/^"(\d+)\/\//';
		if (preg_match($pattern, $etag, $matches)) return $matches[1];
		return false;
	}
	
	/**
	 * @return String the filename extension, the part after last ".",
	 *  empty string if no "." in name, or the only "." is first
	 */
	function getExtension() {
		$pos = strrpos($this->getFilename(), '.');
		if (!$pos) return '';
		return substr($this->getFilename(), $pos+1);
	}
	
	/**
	 * @return the mimetype (or best guess) of the file,
	 *  can be used as value in ContentType header.
	 */
	function getType() {
		// 1: If HEAD, simply get the headers from apache
		if ($this->isLatestRevision()) return $this->_getMimeTypeFromHttpHeaders();
		// 2: If revision != head, get the svn:mime-type property
		$prop = login_getMimeTypeProperty($this->getUrl(), $this->getRevision());
		if ($prop) return $prop;
		// 3: If revision != head, guess the mime type for relevant (common) extensions, use default if not
		// if it exists in HEAD we're lucky
		if ($this->isReadableInHead()) return $this->_getMimeTypeFromHttpHeaders();
		// never look at file contents, too complicated and we don't want to require fileinfo extension
		trigger_error("Could not find content type for this file.", E_USER_ERROR);
	}
	
	function _getMimeTypeFromHttpHeaders() {
		$this->_head();
		if (isset($this->head['Content-Type'])) {
			if ($pos = strpos($this->head['Content-Type'], ';')) {
				return trim(substr($this->head['Content-Type'], 0, $pos));
			}
			return $this->head['Content-Type'];
		}
	}
	
	/**
	 * @return String the "discrete-type" part of the mime type: 
	 * "text" / "image" / "audio" / "video" / "application" /  "message" / "multipart"
	 * @see isPlaintext()
	 */
	function getTypeDiscrete() {
		$t = $this->getType();
		$s = strpos($t,'/');
		if (!$s) trigger_error("This file has an invalid MIME type '$t'.");
		return substr($t, 0, $s);
	}
	
	/**
	 * @return boolean true if the file contains plain text
	 */
	function isPlaintext() {
		// exceptions
		if ($this->getType() == 'application/x-javascript') return true;
		// general rule
		return ($this->getTypeDiscrete() == 'text');
	}
	
	/**
	 * @return the size of the file in bytes
	 */
	function getSize() {
		$this->_read();
		return $this->file['size'];
	}
	
	/**
	 * This is _not_ a getter for the '_revision' field, which may have value HEAD.
	 * @return int Integer revision number, even for HEAD.
	 */
	function getRevision() {
		$this->_read();
		return $this->file['revision'];
	}
	
	/**
	 * @return String Last modified, in xsd:dateTime timestamp format
	 */
	function getDate() {
		$this->_read();
		return $this->file['date'];
	}
	
	/**
	 * @return String Username, author in last commit
	 */
	function getAuthor() {
		$this->_read();
		return $this->file['author'];
	}

	function isLocked() {
		$this->_read();
		return array_key_exists('lockowner', $this->file);
	}
	
	function isLockedByThisUser() {
		if (!$this->isLocked()) return false;
		return ($this->getLockOwner() == $this->getAuthenticatedUser());
	}
	
	function isLockedBySomeoneElse() {
		if (!$this->isLocked()) return false;
		return ($this->getLockOwner() != $this->getAuthenticatedUser());
	}
	
	function getLockOwner() {
		if (!$this->isLocked()) return false;
		return $this->file['lockowner'];
	}
	
	/**
	 * @return String xsd:dateTime formatted timestamp for when the lock was created
	 */
	function getLockCreated() {
		if (!$this->isLocked()) return false;
		return $this->file['lockcreated'];
	}
	
	/**
	 * @return String lock message, "" if missing or empty, false if not locked
	 */
	function getLockComment() {
		if (!$this->isLocked()) return false;
		if (!array_key_exists('lockcomment', $this->file)) return '';
		return $this->file['lockcomment'];
	}
	
	/**
	 * @return boolean true if svn:needs-lock is set (to any value) on this file
	 */
	function isNeedsLock() {
		// TODO implement
		return false;
	}
	
	/**
	 * @return boolean true if this is a branch
	 *  (currently meaning that it lives in /branches/) 
	 */
	function isBranch() {
		$pattern = "/\/branches\//";
		return preg_match($pattern, $this->getPath());
	}
	
	/**
	 * @return String the path of the source this file was copied from
	 */
	function getBranchedFromPath() {
		// TODO need to use log to find this
		return '(branch tracking not implemented)';
	}
	
	/**
	 * @return int the revision of the source for the "svn copy"
	 */
	function getBranchedFromRevision() {
		// TODO need to use log to find this
		return -1;
	}
	
	/**
	 * Reads the contents of the file to a string of characters, even if it is binary
	 */
	function getContents() {
		// TODO this might need a lot of memory
		return implode("\n", $this->getContents());
	}
	
	/**
	 * Reads the contents of the file to a string array, one item per line, without newlines.
	 */
	function getContentsText() {
		$open = new SvnOpen('cat');
		$open->addArgUrlPeg($this->getUrl(), $this->getRevision());
		$open->exec();
		return $open->getOutput();
	}
	
	/**
	 * Sends this file without headers, for embedding into page.
	 * If HTML or XML is to be used in a text area, it should be read with this method.
	 * This method assumes that access has been verified already, so it simply runs the 'cat' command.
	 */
	function sendInline() {
		$open = new SvnOpen('cat');
		$open->addArgUrlPeg($this->getUrl(), $this->getRevision());
		if ($open->passthru()) {
			// failed, but there is nothing we can do about that now
		}
	}
	
	/**
	 * Sends the file without headers, escaped as html.
	 * Escaping method is based on the format. If there is no known escape method for the content type, sendInline is used.
	 */
	function sendInlineHtml() {
		// TODO there is currently no efficient passthru with filter,
		// so the show page must limit size so that we don't use up all memory
		if ($this->getSize()>102400) trigger_error("Can not convert file bigger than 100 kb to HTML.", E_USER_ERROR);
		$text = $this->getContentsText();
		$lines = count($text);
		for ($i=0; $i<$lines; $i++) {
			echo(htmlentities($text[$i]));
			echo("\n");
		}
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
		if ($this->isLatestRevision()) {
			$this->_head();
			return $this->headStatus;
		}
		$this->_read();
		// no error, assume ok
		return 200;
	}
	
	/**
	 * Reads all file information with a 
	 * @return array[String] metadata name=>value
	 */
	function _readInfoSvn() {
		$info = new SvnOpen('list', true);
		$info->addArgRevision($this->_revision);
		$info->addArgUrl($this->url);
		$info->exec();
		return $this->_parseListXml($info->getOutput());
	}
	
	/**
	 * Parses the result of svn list --xml to an associative array
	 *
	 * @param array[String] $xmlArray
	 * @return array[String] metadata entry name => value
	 */
	function _parseListXml($xmlArray) {
		$parsed = array();
		$patternsInOrder = array(
			'path' => '/path="([^"]+)"/',
			'kind' => '/kind="([^"]+)"/',
			'name' => '/<name>([^<]+)</',
			'size' => '/<size>(\d+)</',
			'revision' => '/revision="(\d+)"/',
			'author' => '/<author>([^<]+)</',
			'date' => '/<date>([^<]+)</',
			'locktoken' => '/<token>([^<]+)</',
			'lockowner' => '/<owner>([^<]+)</',
			'lockcomment' => '/<comment>([^<]+)</',
			'lockcreated' => '/<created>([^<]+)</',
		);
		list($n, $p) = each($patternsInOrder);
		for ($i=0; $i<count($xmlArray); $i++) {
			if (preg_match($p, $xmlArray[$i], $matches)) {
				$parsed[$n] = $matches[1];
				if(!(list($n, $p) = each($patternsInOrder))) break;
			} else if ($n == 'lockcomment') { // optional entry
				list($n, $p) = each($patternsInOrder);
				$i--;
			}
		}
		return $parsed;
	}
	
	/**
	 * Not used currently
	 *
	 * @return unknown
	 */
	function _readInfoHttp() {
		// http can not read the actual revision number for HEAD
		return true;
	}
	
}

?>
