<?php
/**
 * Repository commits (c) 2006-1007 Staffan Olsson www.repos.se 
 * Operations that result in a new revision in the repository.
 * 
 * @package edit
 * @see SvnEdit
 * @see FilenameRule
 * @see NewFilenameRule
 * @see FolderWriteAccessRule
 */

require_once( dirname(dirname(__FILE__))."/open/SvnOpenFile.class.php" );
require_once(dirname(dirname(__FILE__)).'/plugins/validation/validation.inc.php');

// ---- standard rules that the pages can instantiate ----
define('REPOS_FILENAME_MAX_LENGTH',200);

define('PARAM_REVPROP_PREFIX','revprop_');

/**
 * Shared validation rule representing file- or foldername restrictions.
 *
 * Not required field. Use Validation::expect(...) to require.
 *
 * Basically same rules as in windows, but max 50 characters,
 * no \/:*?"<> or |.
 * Unlike windows, we don't accept single quote.
 *
 * @package edit
 */
class FilenameRule extends RuleRegexp {
	var $required;
	function __construct($fieldname, $required=true) {
		$this->required = $required;
		parent::__construct($fieldname,
			'may not contain any of the characters +&:*?<>\/| or quotes',
			'/^[^+&\/\\:\*\?+<>\|\'"]+$/');
	}
	function validate($value) {
		if (empty($value)) return $this->required ? 'This is a required field' : null;
		if ($value=='.') return 'The name "." is not a valid filename';
		if ($value=='..') return 'The name ".." is not a valid filename';
		if (mb_strlen($value) > REPOS_FILENAME_MAX_LENGTH) return "max length "+REPOS_FILENAME_MAX_LENGTH; // excluding file extension
		return parent::validate($value);
	}
}

/**
 * Shared validation rule, used before attempting a create,
 * to check that the name is not already in use.
 * This is a common error case, so we don't want to wait for the commit error.
 *
 * @package edit
 */
class NewFilenameRule extends Rule {
	var $_pathPrefix;
	function __construct($fieldname, $pathPrefix='') {
		$this->_pathPrefix = $pathPrefix; // fields first, then parent constructor
		parent::__construct($fieldname, '');
	}
	function validate($fieldvalue) {
		$target = $this->_getPath($fieldvalue);
		$s = new SvnOpenFile($target);
		if ($s->getStatus()==500) trigger_error("Validation aborted because of repository error (status 500) at $target", E_USER_ERROR);
		if ($s->getStatus()==404) {
			$this->_value = $fieldvalue;
			return;
		}
		if ($s->getStatus()==403) return "The URL has access denied, so $target can not be used.";
		if ($s->isFolder()) return 'There is already a folder named "'.basename($target).'". Choose a different name.';
		return 'There is already a file named "'.basename($target).'". Choose a different name.';
	}
	function _getPath($fieldvalue) {
		return $this->_pathPrefix.$fieldvalue;
	}
}

/**
 * Checks that a resource exists
 * and that access is granted publicly or to the acthenticated user.
 * 
 * If the resource requires login, ServiceRequest will forward that transparently.
 * 
 * Operations are validated upon commit, but since 'svn import'
 * will create any missing parent folders in the new path we must
 * validate parent folder explicitly.
 *
 * @package edit
 */
class ResourceExistsRule extends Rule {
	function __construct($fieldname='target') {
		parent::__construct($fieldname, 'The path does not exist in the repository.');
	}
	function valid($fieldvalue) {
		$s = new ServiceRequest(getRepository().$fieldvalue);
		$s->setSkipBody();
		$s->exec();
		if ($s->getStatus() == 403) {
			trigger_error('Access denied to '.$s->uri, E_USER_NOTICE);
		}
		if ($s->getStatus() == 401) {
			if (isLoggedIn()) {
				// TODO prompt for authenticatin again?
				trigger_error('Authentication failed', E_USER_NOTICE);
			} else {
				trigger_error('Authentication not performed', E_USER_ERROR);
			}
		}
		if($s->getStatus() == 301){
			$headers = $s->getResponseHeaders();
			return $headers['Location'] == getRepository().$fieldvalue.'/';
		}
		return $s->getStatus() == 200;
	}	
}

class ResourceExistsAndIsWritableRule extends ResourceExistsRule {
	function __construct($fieldname='target') {
		parent::__construct($fieldname);
	}
	function valid($fieldvalue) {
		$v = parent::valid($fieldvalue);
		// TODO How do we check if write access is allowed
		//  for a folder that does not require login for read access?
		return $v;
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
 * @param String $nextUrl The "return to" URL to suggest for continued browsing, typically a folder
 * @param String $target Target path for the edit result's details page, HEAD
 * @param String $headline The h1 of the resulting page
 * @param String $summary The final word of the resulting page 
 * @package edit
 * @see SvnEdit::show()
 */
function displayEdit(&$presentation, $nextUrl=null, $target=null, $headline=null, $summary=null) {
	if ($target === null && isTargetSet()) {
		$target = getTarget();
	}
	$presentation->assign('target', $target);
	if (!$nextUrl) {
		if (!isTargetSet()) trigger_error("Server error. No target given, nextUrl required.", E_USER_ERROR);
		$nextUrl = getTargetUrl();
		if (!isFolder($nextUrl)) {
			$nextUrl = getParent($nextUrl); // get the parent folder for a file, and the folder itself for a folder
		}
	}
	$presentation->assign('nexturl',$nextUrl);
	$presentation->assign('headline',$headline);
	$presentation->assign('summary',$summary);
	// TODO error presentation for enableRedirectWaiting instead of current wait until timeout
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
 * This method is for the special case where the svn operation returns no message.
 *
 * @param Smarty $presentation
 * @param String $errorMessage
 * @package edit
 */
function displayEditAndExit(&$presentation, $nextUrl=null, $target=null, $errorMessage=null) {
	if (!$errorMessage) $errorMessage = 'Versioning operation failed';
	displayEdit($presentation, $nextUrl, $target, $errorMessage);
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
	
	var $message = ''; // not escaped
	var $commitWithMessage; // allow for example checkout to run without a -m

	/**
	 * Constructor
	 * @param String $subversionOperation svn command line operation, for example mkdir or del.
	 *  It is recommended to use the long name, like 'list' instead of 'ls' because it is more readable.
	 */
	function __construct($subversionOperation) {
		$this->command = new SvnOpen($subversionOperation);
		// default commit message setting, can always be enabled with setMessage
		$this->commitWithMessage = ($subversionOperation=='commit' || $subversionOperation=='import');
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
	 * Like addArgOption but without an option prefix,
	 * for example to set property value.
	 * @param String $argument to be escaped, quoted and added to command
	 */
	function addArg($argument) {
		$this->command->addArg($argument);
	}	
	
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
	 * Uniquely identifies an object in subversion, for a url (history) that may have contained different objects.
	 * @param String $url the URL, just like addArgUrl
	 * @param int|String $revision, the revision number to append as URL@PEG-REV
	 */
	function addArgUrlPeg($url, $revision) {
		$this->command->addArgUrlPeg($url, $revision);
	}	
	
	/**
	 * @param option command line switch, will be added to commandline without encoding or quotes
	 */	
	function addArgOption($option, $value=null, $valueNeedsEscape=true) {
		$this->command->addArgOption($option, $value, $valueNeedsEscape);
	}

	/**
	 * Sets a --with-revprop option on the command, see svn CLI docs.
	 * @param String $revpropName
	 * @param String $revpropValue
	 */
	function addArgRevprop($revpropName, $revpropValue) {
		$this->addArgOption("--with-revprop", "$revpropName=$revpropValue");
	}

	function addArgRevpropsFromPost() {
		foreach ($_POST as $name => $value) {
			if (strBegins($name, PARAM_REVPROP_PREFIX)) {
				$this->addArgRevprop(substr($name, strlen(PARAM_REVPROP_PREFIX)), $value);
			}
		}
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
		$result = $this->execNoDisplay();
		$this->_show($description);
		return $result;
	}
	
	/**
	 * Like exec but does not display result if command fails, good for fallback situations.
	 * @return int the exic code
	 */
	function execNoDisplayOnError($description=null) {
		$result = $this->execNoDisplay();
		if ($result == 0) $this->_show($description);
		return $result;
	}
	
	/**
	 * Executes command without displaying results in Presentation.
	 * @return int the exit code
	 */
	function execNoDisplay() {
		if ($this->commitWithMessage) { // commands expecting a message need this even if it is empty
			$this->addArgMightBeMultiline($this->message, '-m');
		}
		return $this->command->exec();
	}
	
	/**
	 * Typically used for commit message and propset value.
	 * @param $value the argument value
	 * @param $argIfSingleLine null if a position argument, for example "-m" if named
	 * @param $argIfMultiline arg before filename for multiline contents
	 */
	function addArgMightBeMultiline($value, $argIfSingleLine=null, $argIfMultiline='-F') {
		// same functionality as in propedit
		if (strContains($value, "\n") || strpos($value, '-') === 0) {
			// normalize newlines TODO check if windows clients use CRLF
			$value = str_replace("\r\n", "\n", $value);
			// can not preserve newlinees on command line, must use file			
			$argtemp = System::getTempFile();
			$fp = fopen($argtemp, 'w');
			fwrite($fp, $value);
			fclose($fp);
			$this->addArgOption($argIfMultiline, $argtemp);
			// temp file will not be cleaned up automatically because it is not in the wc
			// TODO could be cleaned up after commit using the value of the -F argument
		} else {
			if ($argIfSingleLine) {
				$this->command->addArgOption($argIfSingleLine, $value);	
			} else {
				$this->command->addArg($value);
			}
		}
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
	 * the conclusion, like "Committed revision 123".
	 * This hides output like each downloaded file form a checkout.
	 * It should not hide interesting info, i.e. anything that is not normally printed from the command.
	 * @return result of the subversion operation, empty string if it gave no output
	 */
	function getResult() {
		$op = $this->getOperation();
		$o = $this->getOutput();
		if (!count($o)) return 'No output from operation '.$op;
		// The presentation can always getResults but for commit operations we want hook info in the results
		if ('commit' != $op && 'ci' != $op && 'import' != $op) return $o[count($o)-1];
		// In earlier Repos releases we've removed as much output as possible
		// but we should probably change that so we don't hide errors and hook output.
		// Starting with commit for which we have unit tests.
		$f = array_filter($o, array($this, "_resultFilter"));
		if (!count($o)) trigger_error('Unrecognized output: '.implode("\n", $o), E_USER_ERROR);
		return trim(implode("\n", $f));
	}
	
	/**
	 * @param $line Line from command output
	 * @return true if the line should be displayed
	 */
	function _resultFilter($line) {
		$op = $this->getOperation();
		// Subversion transaction output can be identified on multiple spaces between verb and path
		if (strBegins($line, 'Adding  ')) return false;
		if (strBegins($line, 'Sending  ')) return false;
		// This is not a likely output from hooks
		if (strBegins($line, 'Transmitting file data ')) return false;
		return true;
	}
	
	/**
	 * @return the output from the svn command, all lines except the return code
	 */
	function getOutput() {
		return $this->command->getOutput();
	}
	
	/**
	 * @return the revision number that this operation created upon success,
	 *  if operation failed or output does not contain the revision number return null
	 */
	function getCommittedRevision() {
		if ($this->isSuccessful()) {
			// TODO check after newlines too?
			if (preg_match('/^[a-zA-Z ]+([0-9]+)\./m', $this->getResult(), $rev)) {
				return $rev[1];
			} else {
				// not ok to throw error because some SvnEdit operations like propset are only local
				//trigger_error('Revision number not found in commit output. "'.$this->getResult().'"', E_USER_ERROR);
			}
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
		$logEntry = array(
			'result' => _edit_svnOutput($this->getResult()),
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
		} else if (defined('REPOSTEST')) {
			// running test case, not nessecary to display edit output
		} else {
			trigger_error('Can not display edit output', E_USER_NOTICE);
		}
	}

}

/**
 * Filter svn output so it is safe for presentation
 * @param String $str
 */
function _edit_svnOutput($s) {
	// usability, do-gooding?
	$s = str_replace('Committed revision','Committed version',$s);
	$s = preg_replace('/^svn:/','',$s);
	// system integrity
	$a = System::getApplicationTemp();
	$s = str_ireplace($a, '', $s);
	if (System::isWindows()) $s = str_ireplace(str_replace("/","\\",$a), '', $s);
	return $s;
}
?>
