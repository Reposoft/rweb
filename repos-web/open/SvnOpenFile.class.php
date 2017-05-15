<?php
/**
 * Subversion dir entry abstraction (c) 2006-2007 Staffan Olsson www.repos.se
 * 
 * Combines svn info and list with apache read operations to find
 * all info needed about a dir entry in UI operations,
 * inluding locks, content type and write access detection.
 * 
 * @package open
 * @version $Id$
 */
if (!class_exists('SvnOpen')) require(dirname(__FILE__).'/SvnOpen.class.php');
if (!class_exists('ServiceRequest')) require(dirname(__FILE__).'/ServiceRequest.class.php');

define('MIMETYPE_UNKNOWN', 'application/x-unknown');

// Limit size for open/file/ to not cause problems in:
// - file passthru (does not support filtering so SvnOpenFile does html escape in memory)
// - browser performance
define('REPOS_TEXT_MAXSIZE', 1050000);
define('REPOS_TEXT_MAXSIZE_STR', '1 MB');

/**
 * Convert filesize from bytes to B, kB or MB.
 *
 * @param String $Bytes filesize in bytes
 * @package open
 */
function formatSize($Bytes) {
	if ($Bytes < 1000) {
		return $Bytes . ' B';
	}
	$f = 1.0 * $Bytes / 1024;
	if ($f < 0.995) {
		return number_format($f,1,".","").' kB';
	}
	if ($f < 999.5) {
		return number_format($f,0,".","").' kB';
	}
	$f = $f / 1024;
	if ($f < 0.995) {
		return number_format($f,1,".","").' MB';
	}
	if ($f < 99.95) {
		return number_format($f,0,".","").' MB';
	}
	return number_format($f,0,".","").' MB';
}

// Check if a string is in UTF-8 format
function isUTF8($str) {
	if ($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32")) {
		return true;
	} else {
		return false;
	}
}

// Convert common characters to named or numbered entities
function makeTagEntities($str, $useNamedEntities = 1) {
  // Note that we should use &apos; for the single quote, but IE doesn't like it
  $arrReplace = $useNamedEntities ? array('&#39;','&quot;','&lt;','&gt;') : array('&#39;','&#34;','&#60;','&#62;');
  return str_replace(array("'",'"','<','>'), $arrReplace, $str);
}

// Convert ampersands to named or numbered entities.
// Use regex to skip any that might be part of existing entities.
function makeAmpersandEntities($str, $useNamedEntities = 1) {
  return str_replace('&', $useNamedEntities ? "&amp;" : "&#38;", $str);
  // return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/m", $useNamedEntities ? "&amp;" : "&#38;", $str);
}

/**
 * SvnOpenFile is very convenient, but could be a performance issue
 * if used in many simple tasks where simpler methods would be sufficient.
 * 
 * SvnOpenFile is meant to present details for a file, with the same interface for HEAD and older revisions.
 * If many resources are involved in a request, it is probably not a task for SvnOpenFile.
 * 
 * For example in edit operations, the system validates operations automatically
 * when a commit is attempted, so all we need is proper error handling.
 *
 * @param SvnOpenFile $fromConstructor
 */
function _svnOpenFile_setInstance($fromConstructor) {
	static $instance = null;
	if ($fromConstructor && $instance) trigger_error('Svn file has already been opened in this request', E_USER_ERROR);
	$instance = $fromConstructor;
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
	 * @var boolean true if url is at HEAD, false if url is at revision
	 */
	var $_revisionIsPeg;
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
	 * Setting: be very strict about requested revisions.
	 * These checks are not implemented consistently, but might stop some sloppy browsing/plugins.
	 * @var boolean true to enable checks that specified revisions do exist as commit revisions.
	 */
	var $allowOnlyChangedRevisions = false;
	
	/**
	 * Sets the vital information: path, url and revision string.
	 * Actual calls for data are being made (and cached) when requested with get* and is* methods.
	 *
	 * @param String $path file path, absolute from repository root with leading slash
	 * @param String $revision requested revision, integer/date/string (svn syntax), null for latest
	 * @param boolean $validate false to not validate authentication/authorization immediately
	 * @param boolean $revisionIsPeg set to false if the path is from HEAD but the revision migt be older
	 * @return SvnOpenFile
	 */
	function SvnOpenFile($path, $revision=null, $validate=true, $revisionIsPeg=true) {
		$this->path = $path;
		// TODO split between internal and external use and call getRespositoryInternal where possible
		$this->url = SvnOpenFile::getRepository().$path;
		$this->_revision = $revision;
		// default to peg if explicit revision is set (isRevisionRequested()==true)
		if ($revisionIsPeg === null) {
			$revisionIsPeg = $revision !== null;
		}
		$this->_revisionIsPeg = $revisionIsPeg; 
		_svnOpenFile_setInstance($this);
		if ($validate) $this->validateReadAccess();
	}
	
	/**
	 * Validates authentication and authorization to url.
	 * Because Subversion authorization is not versioned, HEAD can be used
	 * to validate access to old revisions too.
	 * Note that this does NOT validate existence (even if revision is HEAD).
	 * @return SvnOpenFile the instance
	 */
	function validateReadAccess() {
		// HTTP is better than svn for this because it is faster and
		// gives us the auth header immediately for authentication passthru
		// See ServiceRequest _forwardAuthentication
		$this->_head();
		return $this;
	}
	
	/**
	 * Called first in every method that requires metadata
	 */
	function _read() {
		$this->_readNoErr();
		// see _nonexisting(), this is how "not found" was reported when it never triggered error
		if (count($this->file)<2) trigger_error('No info found for "'.$this->url.'"'.
			' at revision '.$this->getRevisionRequestedString(), E_USER_ERROR);
	}
	/**
	 * Does not trigger error if the entry does not exist,
	 * instead sets file to array with only one element, 'path'.
	 */
	function _readNoErr() {
		//if ($this->isFolder()) trigger_error("File operation attempted on a folder.", E_USER_ERROR);
		if (!is_null($this->file)) return;
		$this->file = $this->_readInfoSvn();
		if (is_null($this->file)) trigger_error("Failed to read info for '$path' revision $revision in repository ".getRepository(), E_USER_ERROR);
	}
	
	function _readFile() {
		$this->_read();
		$info = $this->file;
		if ($info['kind'] != 'file') trigger_error("Operation only allowed for files", E_USER_ERROR);
		if (isset($info['size'])) return; // list already read
		$listinfo = $this->_readListSvn();
		$this->file = array_merge($listinfo, $info); // the revision number from svn info is the correct one (not last-changed-revision)
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
		if ($this->headStatus == 0) trigger_error("Could not connect to repository URL ".$this->url.". Might be a temporary error.", E_USER_ERROR);
		//could be a folder//if ($this->headStatus != 200 && $this->headStatus != 404) trigger_error("Unexpected response from target URL '".$this->url."'. Status ".$this->headStatus, E_USER_ERROR);
	}
	
	/**
	 * This might seem like an odd method to have in SvnOpenFile,
	 * but it is good for reuse of code. The class does a best-effort
	 * attempt to return meaningful results for folders, specifically isWritable().
	 * It only does HTTP operations, not svn (the _read() method is not allowed for folders).
	 */
	function isFolder() {
		if ($this->isFolderGuess()) return true;
		return $this->getKind() == 'dir';
	}
	
	/**
	 * Needed to check for folder without accessing svn.
	 * @return true if it is likely that the given path is a folder, i.e. if it ends with slash
	 */
	function isFolderGuess() {
		return strEnds($this->path,'/');
	}
	
	/**
	 * @return boolean true if the entry is a file
	 */
	function isFile() {
		return !$this->isFolder();
	}
	
	/**
	 * @return the repository root url without trailing slash
	 * @static
	 */
	function getRepository() {
		return getRepository();
	}
	
	/**
	 * @return String the username of the account used to access the file, false if not authenticated
	 */
	function getAuthenticatedUser() {
		return SvnOpen::getAuthenticatedUser();
	}
	
	/**
	 * @return boolean true if the path is repository root
	 */
	function isRoot() {
		return $this->path == '/';
	}
	
	function getFilename() {
		if ($this->isRoot()) {
			return getPathName($this->getRepository());
		}
		return getPathName($this->path);
	}
	
	function getFilenameWithoutExtension() {
		$name = $this->getFilename();
		$ext = $this->getExtension();
		if ($ext) {
			return substr($name, 0, strlen($name) - strlen($ext) - 1);
		} else {
			return $name;
		}
	}
	
	/**
	 * Return user friendly type of entry, "file" or "folder", for use in texts.
	 * @return lowercase text that can be used instead of if-else in messages.
	 */
	function getKind2() {
		$k = $this->getKind();
		if ($k == 'dir' && $this->path == '/') return 'repository';
		if ($k == 'dir') return 'folder';
		return $k;
	}
	
	/**
	 * @return the value of subversion's "node kind"
	 */
	function getKind() {
		$this->_read();
		return $this->file['kind'];
	}
	
	function getPath() {
		return $this->path;
	}
	
	function getParentPath() {
		return getParent($this->path);
	}
	
	/**
	 * @return same path if path ends with slash, parent if not
	 */
	function getFolderPath() {
		// For risk of changing auth behavior in pages we don't dare to introduce an info check for this method,
		// although it is quite possible that it would only affect unit tests
		if ($this->isFolderGuess()) {
			return $this->getPath();
		}
		return $this->getParentPath();
	}
	
	/**
	 * @return string The URL for this item, with ?p= or ?r= if a revision is requested.
	 */
	function getUrl() {
		$u = $this->getUrlNoquery();
		if ($this->isRevisionRequested()) {
			if ($this->isRevisionPeg()) {
				$u .= '?p=';
			} else {
				$u .= '?r=';
			}
			$u .= $this->getRevisionRequested();
		}
		return $u;
	}
	
	/**
	 * No-query version of URL.
	 * @return string The URL without query parameters like revision
	 */
	function getUrlNoquery() {
		return $this->url;
	}
	
	/**
	 * @return string Parent URL, even for folders
	 */
	function getParentUrl() {
		return $this->getRepository().$this->getParentPath();
	}
	
	/**
	* @return string Parent URL for files, this URL for folders
	*/
	function getFolderUrl() {
		return $this->getRepository().$this->getFolderPath();
	}
	
	/**
	 * Try to figure out if the revision is the latest,
	 * which is only trivial if revision was given as HEAD.
	 * If the file does not exist, we say there is no latest revision (returns false).
	 * If user has access forbidden (by the ACL) we don't know anything about latest revision,
	 *  so an error is triggered.
	 * @return boolean true if this file is the newest revision.
	 * @see isReadableInHead() to check if the file exists and user has read access.
	 */
	function isLatestRevision() {
		if (!$this->isRevisionRequested()) return true;
		if ($this->getRevisionRequested() == HEAD) return true;
		// need to check the current response code
		$this->_head();
		// if it does not exist in head it has been deleted
		if ($this->headStatus == 404) return false;
		if ($this->headStatus == 403) { // caller can check isReadableInHead to avoid this
			trigger_error('Access denied for resource '. $this->getPath(), E_USER_ERROR);
		}
		// special handling for folders
		if ($this->isFolder()) {
			return false;// after repos 1.3.1 //trigger_error('Operation not supported for folders', E_USER_ERROR);
		}
		// otherwise we assume that the ETag contains the revision number
		$r = $this->_getHeadRevisionFromETag();
		$rr = intval($this->getRevisionRequested()); // 0 if string
		if ($rr && $r !== false) {
			if ($r < $rr && $this->allowOnlyChangedRevisions) {
				trigger_error('The specified revision '.$rr.
					' is newer than the last changed revision of this file', E_USER_WARNING);
			}
			return $r <= $rr;
		}
		// it is unlikely that we come this far
		trigger_error("Could not read revision number of the latest version from repository.", E_USER_ERROR);
		// TODO final: either parse dates and compare or do an svn list call on HEAD and compare revisions
	}
	
	/**
	 * Checks if the file still exists, and the user still has read access to it.
	 * Note that this is not a "peg revision" check, so it might be a different file at the same URL.
	 * @return boolean true if the file exists in current head revision of repository, false otherwise.
	 */
	function isReadableInHead() {
		// Note that even if the revision is HEAD we are not sure it is readable.
		$this->_head();
		return ($this->headStatus == 200);
	}

	function isDownloadAllowed() {
		$rule = isset($_SERVER['REPOS_DOWNLOAD_RULE']) ? $_SERVER['REPOS_DOWNLOAD_RULE'] : '';
		if ($rule) {
			$urlFull = $this->getUrlNoquery();
			$pathFromRoot = substr($urlFull, strpos($urlFull, '/', 8));
			if (preg_match($rule, $pathFromRoot)) return true;
		}
		// backwards compatibility with no env
		return $this->isFile();
	}

	/**
	 * Checks for write access.
	 * Authenticates if prompted by server upon DAV request.
	 * 
	 * Note that read-only is not a versioned property (it is caused by apache configuration).
	 * For all revisions that are _not_ HEAD, this method has to return 'false'.
	 * To detect read-only for files or folders, it is also possible to 
	 *  inspect "svn info --xml [parent folder]" and check if commit autor and date are missing.
	 * 
	 * This method prompts for authentication if it gets 401 from
	 * the server, which means that for publicly readable files it
	 * will result in a login box (no resource should be publicly writable).
	 * 
	 * Results are cached throughout the request because this is an expensive operation.
	 * 
	 * @see _svnFileIsWritable
	 * @see isWriteAllow
	 * @return boolean true if resource is writable in DAV, 
	 *  false for status 404 and 403 and old revisions. 
	 *  Locked files may still be writable, see integration tests and isWriteAllow().
	 */	
	function isWritable() {
		static $_writable = null;
		if (is_null($_writable)) {
			$_writable = $this->_isWritable();
		}
		return $_writable;
	}
	
	/**
	 * Results not cached.
	 */
	function _isWritable() {
		if (!$this->isLatestRevision()) return false;
		if ($this->getStatus() == 404) return false;
		// TODO using noquery here but shouldn't we say no immediately if rev is requested and not HEAD? See isLatestRevision issues though.
		if ($this->isFolder()) return _svnFolderIsWritable($this->getUrlNoquery());
		return _svnFileIsWritable($this->getUrlNoquery());
	}
	
	/**
	 * Method created as a shorthand for isWritable() && !isLockedBySomeoneElse().
	 * @return true if the user is allowed to commit a new version,
	 *  i.e. the file isWritable and not isLockedBySomeoneElse
	 */
	function isWriteAllow() {
		if (!$this->isWritable()) return false;
		// TODO isn't it quite costly to check for lock every time?
		// maybe isWritable should return false for locked files?
		if ($this->isLockedBySomeoneElse()) return false;
		return true;
	}
	
	/**
	 * Subversion usually puts the revision number in the ETag header.
	 * WARNING: This is the "Last modified revision" and does not change
	 * at for example copy.
	 * @return the revision number if found, boolean false if not
	 *  (if revision number can be 0, use ===)
	 */
	function _getHeadRevisionFromETag() {
		$this->_head();
		if (!array_key_exists('ETag', $this->head)) {
			trigger_error('Server error. ETag header missing for repository resource.', E_USER_ERROR);
		}
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
		$name = $this->getFilename();
		if (strlen($name) > 7 && strEnds($name, '.tar.gz')) return 'tar.gz';
		$pos = strrpos($name, '.');
		if (!$pos) return '';
		return substr($name, $pos+1);
	}
	
	/**
	 * Gets a value for Content-Type headers,
	 * from 1) apache 2) svn or 3) guess.
	 * @return the mimetype (or best guess) for the file,
	 * 	or 'application/x-unknown' if Repos has no guess.
	 */
	function getType() {
		// 1: If this is the HEAD revision, simply get the headers from apache
		if ($this->isLatestRevision()) return $this->_getMimeTypeFromHttpHeaders();
		// 2: If revision != head, get the svn:mime-type property
		$prop = $this->getMimeTypePropertyValue();
		if ($prop) return $prop;
		// 3: If revision != head, guess the mime type for relevant (common) extensions, use default if not.
		// If the file exists in HEAD we can safely assume that the content type has not been changed.
		if ($this->isReadableInHead()) return $this->_getMimeTypeFromHttpHeaders();
		// TODO guess mimetype (based on filename extension)
		return MIMETYPE_UNKNOWN;
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
	 * @return boolean true if the file is displayable as HTML in a browser
	 */
	function isHtml() {
		return $this->getType() == 'text/html';
	}

	/**
	 * @return the size of the file in bytes
	 */
	function getSize() {
		if ($this->isFolder()) return false;
		$this->_readFile();
		return $this->file['size'];
	}
	
	/**
	 * @return boolean true if an explicit revision (number/string) was set when creating this instance
	 */
	function isRevisionRequested() {
		return $this->_revision !== null;
	}
	
	/**
	 * @return boolean true if revision was requested as peg, 
	 *  or not requested and getRevision returns a proper peg which we assume it does
	 */
	function isRevisionPeg() {
		return $this->_revisionIsPeg;
	}
	
	/**
	 * Helper method to append revision to query string only if it was speficied for this page.
	 * @return String &rev=requested-revision or empty string if no explicit revision
	 */
	function getRevParam() {
		if (!$this->isRevisionRequested()) return '';
		return '&rev='.$this->getRevisionRequested();
	}
	
	/**
	 * Returns not-null revision regardless of input.
	 * @return String requested revision or HEAD (constant) if unspecified
	 */
	function getRevisionRequestedString() {
		if (!$this->isRevisionRequested()) {
			return HEAD;
		}
		return "{$this->getRevisionRequested()}";
	}
	
	/**
	 * Returns "entry" revision,
	 * also called "path revision" as opposed to "commit revision",
	 * meaning that it is the same as the given revision number for peg/rev operations
	 * and head rev when revision is unspecified.
	 * Note that to retrieve info or contents, this revision number
	 * must be combined with the url at the given revision (or HEAD if not isRevisionRequested).
	 * @return int Integer revision number, even for HEAD.
	 */
	function getRevision() {
		$this->_read();
		return $this->file['revision'];
	}
	
	/**
	 * @return String|int the given revision when initializing this instance
	 */
	function getRevisionRequested() {
		return $this->_revision;
	}
	
	/**
	 * The revision for last change of this item.
	 * Note that this change might have occurred at a different URL than getUrl().
	 * @return int Subversion concept, != revision for example if file is unchanged inside copy of tree
	 */
	function getRevisionLastChanged() {
		$this->_read();
		return $this->file['lastChangedRevision'];
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
		if (!isset($this->file['author'])) {
			return ''; // svn xml omits the author node if author is unknown
		}
		return $this->file['author'];
	}

	function isLocked() {
		if ($this->isFolder()) return false;
		$this->_readFile();
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
		trigger_error('isNeedsLock not implemented', E_USER_ERROR);
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
		trigger_error('(branch tracking not implemented)', E_USER_ERROR);
	}
	
	/**
	 * @return int the revision of the source for the "svn copy"
	 */
	function getBranchedFromRevision() {
		// TODO need to use log to find this
		return -1;
	}
	
	/**
	 * Sets URL and Revision for SvnOpen object based on the constructor arguments for this instance.
	 * @param $svnOpen SvnOpen instance
	 */
	function _specifyUrlAndRev($svnOpen) {
		$svnOpen->addArgUrlRev($this->getUrlNoquery(), 
				$this->getRevisionRequestedString(),
				$this->isRevisionPeg());	
	}
	
	/**
	 * Returns the value of the svn:mime-type property of a file with revision number.
	 * @return String mime type, or false if property not set.
	 */
	function getMimeTypePropertyValue() {
		$cmd = new SvnOpen('propget');
		$cmd->addArgOption('svn:mime-type');
		$this->_specifyUrlAndRev($cmd);
		$cmd->exec();
		if ($cmd->getExitcode()) trigger_error("Failed to propget path '{$this->getPath()}' revision {$this->getRevisionRequestedString()} (peg={$this->isRevisionPeg()}).", E_USER_ERROR );
		$result = $cmd->getOutput();
		if (count($result) == 0) { // mime type property not set, return default
			return false;
		}
		return $result[0];
	}

	/**
	 * Reads the contents of the file to a string of characters, even if it is binary
	 */
	function getContents() {
		$open = new SvnOpen('cat');
		$this->_specifyUrlAndRev($open);
		// exec can not be used for reading contents, see REPOS-58
		ob_start();
		// can not do flush because then no headers can be sent, ob_flush();
		if (ob_get_contents()) trigger_error('The output buffer is already in use. Can not read contents.', E_USER_ERROR);
		$result = $open->passthru();
		if ($result) trigger_error('Could not read file from svn', E_USER_ERROR);
		$contents = ob_get_clean();
		// TODO how do we handle \r\n? In Edit in Repos we always convert them to plain newline.
		return str_replace("\r\n", "\n", $contents);
	}
	
	/**
	 * Reads the contents of the file to a string array, one item per line, without newlines.
	 */
	function getContentsText() {
		return explode("\n", $this->getContents());
	}
	
	/**
	 * Sends this file without headers, for embedding into page.
	 * If HTML or XML is to be used in a text area, it should be read with this method.
	 * This method assumes that access has been verified already, so it simply runs the 'cat' command.
	 */
	function sendInline() {
		$open = new SvnOpen('cat');
		$this->_specifyUrlAndRev($open);
		if ($open->passthru()) {
			// failed, but there is nothing we can do about that now
		}
	}
	
	/**
	 * Sends the file without headers, escaped as html.
	 * Escaping method is based on the format. If there is no known escape method for the content type, sendInline is used.
	 */
	function sendInlineHtml() {
		if ($this->getSize() > REPOS_TEXT_MAXSIZE) {
			trigger_error("Can not convert file bigger than " . REPOS_TEXT_MAXSIZE_STR
				. " to HTML.", E_USER_ERROR);
		}
		$text = $this->getContents();
		if (!isUTF8($text)){
			$text = mb_convert_encoding($text, "UTF-8", "ASCII");
		}
		$text = makeAmpersandEntities($text);
		$text = makeTagEntities($text);
		echo $text;
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
		$this->_readNoErr();
		if (count($this->file)<2) return 404;
		// no error, assume ok
		return 200;
	}
	
	function _nonexisting() {
		return array('path' => $this->path);
	}
	
	/**
	 * CMS service to read info.
	 * @return array like the one returned from sax parsing (which is not supported in Quercus)
	 */
	function _readInfoJava() {
		return getReposJavaBridge()->getInfo($this->getRepository(), $this->getPath());
	}
	
	/**
	 * Reads all file information with a 
	 * @return array[String] metadata name=>value, empty array or array with only path if file does not exist
	 */
	function _readInfoSvn() {
		if (isQuercus()) {
			return $this->_readInfoJava();
		}
		// the reason we do 'list' and not 'info' is that 'info' does not contain file size
		$info = new SvnOpen('info', true);
		$this->_specifyUrlAndRev($info);
		if ($info->exec()) {
			// error message instead: trigger_error('Failure to read '.$this->getUrlNoquery()." from svn. \n".implode("\n", $info->getOutput()), E_USER_ERROR);
			return $this->_nonexisting();
		}		
		$result = $info->getOutput();
		$parsed = $this->_parseInfoXml($result);
		if (preg_match('/non-existent/', $result[0].$result[4])) return $this->_nonexisting(); // does not exist in svn
		return $parsed;
	}
	
	/**
	 * For files: Reads size and lock info from svn list. 
	 * @return array[String]
	 */
	function _readListSvn() {
		$info = new SvnOpen('list', true);
		$this->_specifyUrlAndRev($info);
		if ($info->exec()) trigger_error("Could not read file $this->url from svn.", E_USER_ERROR);
		$result = $info->getOutput();
		$listinfo = $this->_parseListXml($result);
		return $listinfo;
	}
	
	/**
	 * Parses the result of svn list --xml to an associative array,
	 * without the use of an xml library
	 *
	 * @param array[String] $xmlArray
	 * @return array[String] metadata entry name => value
	 */
	function _parseListXml($xmlArray) {
		return $this->xml_parseSax($xmlArray);
	}
	
	/**
	 * For folders
	 * @param $xmlArray
	 * @return unknown_type
	 */
	function _parseInfoXml($xmlArray) {
		return $this->xml_parseSax($xmlArray);
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
	
	
	// instance field needed for sax parsing
	var $_xml_fields = array();
	var $_xml_name = null;
	var $_xml_text = null;
	var $_xml_lock = false;
	
	/**
	 * Convert both info and list xml to item info array.
	 * $return parsed info fields, our own keys being quite close to subversion terminology
	 */
	function xml_parseSax($xmlArray) {
		$this->_xml_fields = array();
		$this->_xml_name = null;
		$xml_parser = xml_parser_create();
		xml_set_element_handler($xml_parser, array(&$this, '_xml_start'), array(&$this, '_xml_end'));
		xml_set_character_data_handler($xml_parser, array(&$this, '_xml_data'));
		for ($i = 0; $i < count($xmlArray);) {
			if (!xml_parse($xml_parser, $xmlArray[$i]."\n", count($xmlArray) == $i++)) {
				trigger_error(sprintf("XML error: %s at line %d",
						xml_error_string(xml_get_error_code($xml_parser)),
						xml_get_current_line_number($xml_parser)), E_USER_ERROR);
			}
		}
		xml_parser_free($xml_parser);
		// extra rules, legacy, could probably be in getters but tests use the result array directly
		if (1 >= count($this->_xml_fields)) {
			return array_merge($this->_xml_fields, $this->_nonexisting());
		}
		if (!array_key_exists('name', $this->_xml_fields) && array_key_exists('path', $this->_xml_fields)) {
			$this->_xml_fields['name'] = $this->_xml_fields['path'];
		}
		if (!array_key_exists('author', $this->_xml_fields)) {
			$this->_xml_fields['author'] = null;
		}
		return $this->_xml_fields;
	}
	
	/**
	 * php sax handlers for xml_parseSax
	 */
	function _xml_start($parser, $name, $attrs) {
		if ($name == 'LOCK') $this->_xml_lock = true;		
		$this->_xml_text = null;
		$this->_xml_name = ($this->_xml_lock ? 'lock' : '') . $name;
		if ($name == 'COMMIT') {
			$this->_xml_fields['lastChangedRevision'] = $attrs['REVISION'];
			if (!array_key_exists('revision', $this->_xml_fields)) {
				$this->_xml_fields['revision'] = $attrs['REVISION'];
			}
		} else {
			foreach ($attrs as $key => $value) {
				$this->_xml_fields[strtolower($key)] = $value;
			}
		}
	}
	
	function _xml_end($parser, $name) {
		if ($name == 'LOCK') $this->_xml_lock = false;
		if ($this->_xml_name && !is_null($this->_xml_text)) {
			$this->_xml_fields[strtolower($this->_xml_name)] = trim($this->_xml_text);
		}
	}
	
	function _xml_data($parser, $data) {
		if ($this->_xml_name) {
			if (is_null($this->_xml_text)) $this->_xml_text = '';
			$this->_xml_text .= $data;
		}
	}
	
}

?>
