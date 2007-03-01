<?php
/**
 * Operations that result in a new revision in the repository.
 * 
 * @package edit
 * @see SvnEdit
 * @see FilenameRule
 * @see NewFilenameRule
 * @see FolderWriteAccessRule
 */
require_once( dirname(dirname(__FILE__))."/open/SvnOpen.class.php" );
addPlugin('validation');

/**
 * Tries a resource path in current HEAD, for the current user, returning status code.
 * @param $target the path in the current repository; accepts folder names without tailing slash.
 * @param boolean $login for disabling login in unit tests
 * @return 0 = does not exist, -1 = access denied, 1 = folder, 2 = file, boolean FALSE if undefined
 * @package edit
 * @deprecated REPOS-15, currently it is only used from here
 */
function login_getResourceType($target, $login=true) {
	$url = getTargetUrl($target);
	if (substr_count($url, '://')!=1) trigger_error("The URL \"$url\" is invalid", E_USER_WARNING); // remove when not frequent error
	$request = new ServiceRequest($url, array(), $login);
	$request->setSkipBody();
	$request->exec();
	$s = $request->getStatus();
	$headers = $request->getResponseHeaders();
	if ($s==301 && rawurldecode($headers['Location'])==$url.'/') return 1; // need to decode to handle UTF-8 chars
	if ($s==404) return 0;
	if ($s==403) return -1;
	if ($s==200) return _isHttpHeadersForFolder($headers) ? 1 : 2;
	trigger_error("Unexpected return code $s for URL $url", E_USER_ERROR);
}
function _isHttpHeadersForFolder($headers) {
	if (!$headers['Content-Type']='text/xml') return false;
	return !isset($headers['Content-Length']);
}
 
// ---- standard rules that the pages can instantiate ----

/**
 * Shared validation rule representing file- or foldername.
 * 
 * Not required field. Use Validation::expect(...) to require.
 * 
 * Basically same rules as in windows, but max 50 characters, 
 * no \/:*?"<> or |.
 * 
 * @package edit
 */
class FilenameRule extends RuleEreg {
	var $required;
	function FilenameRule($fieldname, $required='true') {
		$this->required = $required;
		$this->RuleEreg($fieldname, 
			'may not contain any of the characters \/:*?<>|! or quotes', 
			'^[^\\/:*?<>|\'"!]+$');
	}
	function validate($value) {
		if (empty($value)) return $this->required ? 'This is a required field' : null;
		if ($value=='.') return 'The name "." is not a valid filename';
		if ($value=='..') return 'The name ".." is not a valid filename';
		if (strlen($value) > 50) return "max length 50";
		return parent::validate($value);
	}
}

/**
 * Shared validation rule to check if the name for a new file or folder is valid
 * 
 * @package edit
 */
class NewFilenameRule extends Rule {
	var $_pathPrefix;
	function NewFilenameRule($fieldname, $pathPrefix='') {
		$this->_pathPrefix = $pathPrefix; // fields first, then parent constructor
		$this->Rule($fieldname, '');
	}
	function validate($fieldvalue) {
		$target = $this->_getPath($fieldvalue);
		$s = $this->_getResourceType($target);
		if ($s < 0) return "The URL has access denied, so $target can not be used.";
		if ($s == 1) return 'There is already a folder named "'.basename($target).'". Chose a different name.';
		if ($s == 2) return 'There is already a file named "'.basename($target).'". Chose a different name.';
	}
	function _getPath($fieldvalue) {
		return $this->_pathPrefix.$fieldvalue;
	}
	function _getResourceType($path) {
		return login_getResourceType($path);
	}
}

/**
 * Shared validation rule for operations that create new contents (file or folder) in a folder.
 * The repository browser, without javascript addons, does not check such things (because it is rare)
 * but before an upload or create page is presented it has to be checked.
 * 
 * No check is needed for read access, because for read-only folders 
 * you can get to the command only by modifying URLs
 * and in that case there will be an error message when the operation is attempted. 
 * 
 * TODO do we really need this as a validation rule. it is never a form field, or is it? service parameter?
 * 
 * TODO work in progress
 * 
 * @package edit
 */
class FolderWriteAccessRule extends Rule {
	function FolderWriteAccessRule($fieldname='target') {
		$this->Rule($fieldname, 'Your username does not have write access to this folder');
	}
	function validate($fieldvalue) {
		if (!isFolder($fieldvalue)) trigger_error('Target was expected to be a folder', E_USER_ERROR);
		
	}
	/**
	 * Checks if the current logged in user has write privileges on a folder in the repository.
	 *
	 * @param String $targetFolder the folder path in the repository, begins with slash
	 */
	function hasWriteAccess($targetFolder) {
		$url = getRepository() . $targetFolder;
		return _svnResourceIsWritable($url);
	}
}

// ---- presentation support ----

/**
 * All 'edit' operations have the same edit_done presentation template,
 * so they need shared presentation logic.
 * 
 * Called after a series of SvnEdit->exec to display the page to the user.
 * 
 * The last Edit's success status decides if the page should say error or done.
 * 
 * @param Presentation $presentation the page to the user
 * @param String $nextUrl URL to suggest in the link to next page
 * @param String $headline The h1 of the resulting page
 * @param String $summary The final word of the resulting page 
 * @package edit
 * @see SvnEdit::show()
 */
function displayEdit(&$presentation, $nextUrl=null, $headline=null, $summary=null) {
	if (isTargetSet()) {
		$presentation->assign('target', getTarget());
	}
	if (!$nextUrl) {
		if (!isTargetSet()) trigger_error("Server error. No target given, nextUrl required.", E_USER_ERROR);
		$nextUrl = dirname(getTargetUrl()).'/'; // get the parent folder for a file, and the folder itself for a folder
	}
	$presentation->assign('nexturl',$nextUrl);
	$presentation->assign('headline',$headline);
	$presentation->assign('summary',$summary);
	$presentation->enableRedirect();
	//exit;
	$presentation->display(dirname(__FILE__) . '/edit_done.html');
}

/**
 * Used if the current task should be aborted with an error message, 
 * but instead of the standard error page 
 * show the information from Edit->_show calls.
 * 
 * Instead of:
 * <code>
 * $edit->exec();
 * if (!$edit->isSuccessful()) {
 * displayEdit($presentation, ...);
 * exit;
 * }
 * </code>
 * you write
 * <code>
 * $edit->exec();
 * if (error) displayEditAndExit(...)
 * // or 
 * if ($edit->exec()) displayEditAndExit(...)
 * </code>
 * 
 * Server errors should still be reported with trigger_error('...', E_USER_ERROR);
 * 
 * This method is for the special case where the 
 *
 * @param Smarty $presentation
 * @param String $errorMessage
 * @package edit
 */
function displayEditAndExit(&$presentation, $nextUrl=null, $errorMessage=null) {
	if (!$errorMessage) $errorMessage = 'Versioning operation failed';
	displayEdit($presentation, $nextUrl, $errorMessage);
	exit;
}

// ---- the class ----

/**
 * The repository write operation class, representing an SVN operation and the result.
 * 
 * An upload could be a series of edit like:
 * <code>
 * $wc = System::getTempFolder('uploads');
 * $checkout = new SvnEdit('checkout');
 * ... set arguments ...
 * $checkout->exec(); // calls _show() after execution
 * ... copy new file to wc ...
 * $update = new SvnEdit('update');
 * ...
 * $commit = new SvnEdit('commit');
 * $commit->setMessage("message in log and in result page");
 * ...
 * displayEdit($presentation, ...);
 * </code>
 * 
 * For working copy operations that are not 'svn',
 * or are 'svn' but do not take a --username argument,
 * use {@link Command}.
 * 
 * For svn operations that are part of the application logic
 * (like checking the properties of a file) and should not be
 * presented to the user, use {@link SvnOpen}.
 */ 
class SvnEdit {
	/**
	 * @var SvnOpen
	 */
	var $command;
	
	var $message; // not escaped
	var $commitWithMessage = false; // allow for example checkout to run without a -m

	/**
	 * Constructor
	 * @param String $subversionOperation svn command line operation, for example mkdir or del.
	 *  It is recommended to use the long name, like 'list' instead of 'ls' because it is more readable.
	 */
	function SvnEdit($subversionOperation) {
		$this->command = new SvnOpen($subversionOperation);
	}
	
	/**
	 * @param commitMessage The comments to save in svn log.
	 *  Message should not be surrounded with quotes, because it is escaped when the command is created
	 */
	function setMessage($commitMessage) {
		$this->message = $commitMessage;
		$this->commitWithMessage = true;
	}

	// different addArgument functions to be able to adapt encoding

	/**
	 * @param pathElement filename or directory name
	 *  Filenames and paths are expected to be properly command-line or URL encoded (this function does not know which)
	 */
	function addArgFilename($pathElement) {
		$this->command->addArgPath($pathElement);
	}
	
	/**
	 * @param name absolute or relative path with slashes
	 *  Path is expected to be properly adapted to the OS already (see toPath and toShellEncoding)
	 */
	function addArgPath($path) {
		$this->command->addArgPath($path);
	}

	/**
	 * @param url complete url
	 */	
	function addArgUrl($url) {
		$this->command->addArgUrl($url);
	}
	
	/**
	 * @param option command line switch, will be added to commandline without encoding or quotes
	 */	
	function addArgOption($option, $value=null, $valueNeedsEscape=true) {
		$this->command->addArgOption($option, $value, $valueNeedsEscape);
	}

	/**
	 * Replaces the current arguments array.
	 * Use addArgument instead if existing arguments should not be removed.
	 * @param boolean $arrArgumentsInOrder The arguments to the command ordered according to the svn reference
	function _setArguments($arrArgumentsInOrder) {
		$this->args = $arrArgumentsInOrder;
	}
	 */
	
	/**
	 * @return String the subversion command name
	 */
	function getOperation() {
		return $this->command->getOperation();
	}
	
	/**
	 * The arguments should be handled with care, because they reveal system internals.
	 * Also this function reconstructs the arguments, repeating the logic from the
	 * exec call, and then sanitized with regexps, so it is not efficient.
	 * @return String the custom arguments to the svn operation
	 */
	function _getArgumentsString() {
		return $this->command->_getArgumentsString();
	}
	
	/**
	 * @return string the log message if this is an operation that commits to the repository, null if not
	 */
	function getMessage() {
		if (!$this->commitWithMessage) return null;
		return $this->message;
	}
	
	/**
	 * Runs the operation securely (no risk for shell command injection).
	 * Then calls <code>show(Presentation::getInstance())</code>.
	 * Use SvnOpen instead of SvnEdit for operations that are not meaningful to the user.
	 * @return int the exit code
	 */
	function exec($description=null) {
		if ($this->commitWithMessage) { // commands expecting a message need this even if it is empty
			$this->command->addArgOption('-m', $this->message);
		}
		$result = $this->command->exec();
		$this->_show($description);
		return $result;
	}
	
	/**
	 * @return true if operation completed successfuly
	 */
	function isSuccessful() {
		return $this->getExitcode() == 0;
	}
	
	/**
	 * @return int the exit code of the operation, normally 0 if successful
	 */
	function getExitcode() {
		return $this->command->getExitcode();
	}
	
	/**
	 * Returns the last line of the command output, which usually contains
	 * the conclusion, like "Committed revision 123"
	 * @return result of the subversion operation, empty string if it gave no output
	 */
	function getResult() {
		$o = $this->getOutput();
		if (count($o) > 0) return $o[count($o)-1];
		return 'No output from operation '.$this->getOperation();
	}
	
	/**
	 * @return the output from the svn command, all lines except the return code
	 */
	function getOutput() {
		return $this->command->getOutput();
	}
	
	/**
	 * @return the revision number that this operation created upon success
	 */
	function getCommittedRevision() {
		if ($this->isSuccessful()) {
			$match = ereg('^[a-zA-Z ]+([0-9]+)', $this->getResult(), $rev);
			return $rev[1];
		}
		return null;
	}
	
	/**
	 * Present the result of this operation in the Edit smarty template
	 * and then returns so that the task can continue.
	 * 
	 * Called automatically by {@link SvnEdit::exec()}
	 * 
	 * @param String $description a custom summary line for this operation, 
	 *  summary lines will always be visible, use \n as line break
	 */
	function _show($description=null) {
		$result = $this->getResult();
		$logEntry = array(
			'result' => $this->getResult(),
			'operation' => $this->getOperation(),
			'message' => $this->getMessage(),
			'successful' => $this->isSuccessful(),
			'revision' => $this->getCommittedRevision(),
			'output' => implode("\n", $this->getOutput())
		);
		$logEntry['description'] = $description;
		
		if (class_exists('Presentation')) {
			$smartyTemplate = Presentation::getInstance();
			$smartyTemplate->append('log', $logEntry);
			// overwrite existing values, so that the last command decides the result
			$smartyTemplate->assign($logEntry);	
		} else {
			// TODO call report?
		}
	}

}
?>
