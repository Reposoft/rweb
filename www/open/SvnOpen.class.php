<?php
/**
 * Represents a subversion read operation which does not need a working copy.
 * 
 * TODO this is a work in progress. functions are being converted from login.inc.php
 * @package open
 */

// use test mocks if they define global function targetLogin()
if (!function_exists('targetLogin')) require(dirname(dirname(__FILE__)).'/account/login.inc.php');

if (!class_exists('Command')) require(dirname(dirname(__FILE__)).'/conf/Command.class.php');
require_once(dirname(dirname(__FILE__)).'/plugins/validation/validation.inc.php');

define('HEAD','HEAD');

// *** Subversion client usage ***
define('SVN_CONFIG_DIR', _getConfigFolder().'svn-config-dir' . DIRECTORY_SEPARATOR);
if (!file_exists(SVN_CONFIG_DIR)) {
	trigger_error('Config folder for svn commands does not exist. '.
		'Run \'svn --config-dir "'.SVN_CONFIG_DIR.'" info\' (preferrably as webserver user) to create.', E_USER_ERROR);
}

/**
 * @return Mandatory arguments to the svn command, safe for command line (config dir path is escaped)
 * @package open
 */
function _svnopen_getSvnSwitches() {
	$auth = '--username='.Command::_escapeArgument(getReposUser()).' --password='.Command::_escapeArgument(_getReposPass()).' --no-auth-cache';
	$options = '--non-interactive --config-dir '.Command::_escapeArgument(SVN_CONFIG_DIR);
	return $auth.' '.$options;
}

/**
 * @return revision number from parameters (safe as command argument), false if not set
 * @deprecated use RevisionRule instead
 */
function getRevision($rev = false) {
	if (!$rev) {
		if(!isset($_GET['rev'])) {
			return false;
		}
		$rev = $_GET['rev'];
	}
	if (is_numeric($rev)) {
		return $rev;
	}
	$accepted = array('HEAD');
	if (in_array($rev, $accepted)) {
		return $rev;
	}
	trigger_error("Error. Revision number '$rev' is not valid.", E_USER_ERROR);
}

/**
 * A tricky operation, not supported by svn, is to figure out if a file or folder is writable by
 * the user, without trying a write operation.
 * This is our secret.
 * @param String $url The absolute URL to check.
 * @return boolean true if the current user has write access to the file or folder (false if read-only)
 * @package open
 */
function _svnResourceIsWritable($url) {
	$r = new ServiceRequest($url);
	$r->setCustomHttpMethod('LOCK');
	// Use If-Match to make dummy request that does not cause an entry in the error log
	// If the server does not understand this, we'll soon have locked files all over the repository
	$r->setRequestHeader('If-Match', '"shouldnevermatch"');
	$r->exec();
	if ($r->getStatus() == 412) return true;
	if ($r->getStatus() == 403) return false;
	if ($r->getStatus() == 200) {
		// unlock before error message?
	}
	trigger_error('Server configuration error. Could not check if resource is read-only. Status '.$r->getStatus());
}

/**
 * Validates revision (number or string) parameters.
 */
class RevisionRule extends Rule {
	
	function RevisionRule($fieldname='rev', $message='Not a valid revision number') {	
		$this->Rule($fieldname, $message);
	}
	
	function valid($value) {
		if (!$value) return true; // Use Validation::expect to require a valud
		if (is_numeric($value) && $value >=0 ) return true;
		if ($value == HEAD) return true;
		if (strpos($value,'{')==0 && strrpos($value, '}')==strlen($value)-1) return true;
		// no other keywords than HEAD accepted
		return false;
	}
	
}

/**
 * Runs an SVN command that results in text output (repository information).
 * 
 * Content type can be either text/plain or text/xml.
 * 
 * @see Command for the basic functionality
 * @see SvnOpenFile for reading file contents
 * @package open
 */
class SvnOpen {
	
	/**
	 * The command instance that we delegate to (preferred over subclassing).
	 * @var Command
	 */
	var $command;
	
	// store the svn operation for reference
	var $operation;
	
	/**
	 * Creates the command representation.
	 *
	 * @param String $subversionOperation like list or info
	 * @param boolean $asXml set to true to add the --xml parameter (allowed only if the svn command accepts it)
	 * @return SvnOpen
	 */
	function SvnOpen($subversionOperation, $asXml=false) {
		$this->operation = $subversionOperation;
		$this->command = new Command('svn');
		$this->_addSvnOptions();
		$this->command->addArgOption($subversionOperation);
		if ($asXml) $this->command->addArgOption('--xml');
	}
	
	/**
	 * All svn requests are made as the current authenticated user.
	 * @return String username
	 * @static 
	 */
	function getAuthenticatedUser() {
		return getReposUser();
	}
	
	function _addSvnOptions() {
		$this->command->addArgOption(_svnopen_getSvnSwitches());
	}
	
	/**
	 * @param String $url full http or https URL, not urlencoded
	 */
	function addArgUrl($url) {
		$url = urlEncodeNames($url); // allow UTF-8 characters in url on windows too (or do we get problems with this)
		$this->command->addArg($url);	
	}
	
	/**
	 * Uniquely identifies an object in subversion, for a url (history) that may have contained different objects.
	 * See http://svnbook.red-bean.com/nightly/en/svn-book.html#svn.advanced.pegrevs
	 * @param String $url the URL, just like addArgUrl
	 * @param int|String $revision, the revision number to append as URL@PEG-REV
	 */
	function addArgUrlPeg($url, $revision) {
		$url = urlEncodeNames($url).'@'.$revision;
		$this->command->addArg($url);
	}
	
	/**
	 * @param String $path filename or valid local path
	 */
	function addArgPath($path) {
		$this->command->addArg($path);
	}
	
	function addArgRevision($revision) {
		$this->command->addArgOption('-r', $revision);
	}
	
	function addArgRevisionRange($revisionRange) {
		$this->addArgRevision($revisionRange);
	}
	
	function addArgOption($option, $value=null, $valueNeedsEscape=true) {
		$this->command->addArgOption($option, $value, $valueNeedsEscape);
	}
	
	/**
	 * @return String the svn operation for the command
	 */
	function getOperation() {
		return $this->operation;
	}
	
	/**
	 * The arguments should be handled with care, because they reveal system internals.
	 * Also this function reconstructs the arguments, repeating the logic from the
	 * exec call, and then sanitized with regexps, so it is not efficient.
	 * @return String the custom arguments to the svn operation
	 */
	function _getArgumentsString() {
		$arg = $this->command->_getArgumentsString();
		return trim(preg_replace(array(
			'/--username[=\s]+"?[^"]*"?\s+/',
			'/--password[=\s]+"?[^"]*"?\s+/',
			'/--no-auth-cache\s+/',
			'/--non-interactive\s+/',
			'/--config-dir[=\s]+"?[^"]*"?\s+/',
			'/'.$this->getOperation().'\s+/'
		), array('','','','','',''), $arg, 1));
	}
	
	/**
	 * Runs the svn command
	 * @return int the exit code
	 */
	function exec() {
		return $this->command->exec();
	}
	
	/**
	 * Passes the command output directly to browser without buffering,
	 * and also without error handling.
	 * This method should only be used for administration tasks. Useful when output is large.
	 * @return int the exit code, generally 0 if successful
	 */
	function passthru() {
		return $this->command->passthru();
	}
	
	function getExitcode() {
		return $this->command->getExitcode();
	}
	
	function getOutput() {
		return $this->command->getOutput();
	}
	
	function getContentLength() {
		return $this->command->getContentLength();
	}
	
}

?>