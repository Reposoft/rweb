<?php
/**
 * Operations that result in a new revision in the repository.
 * 
 * @package edit
 */

// TODO rename to SvnEdit.class.php, delegate to SvnOpen, add getCommittedRevision
// common functionality in the edit tools
require_once( dirname(dirname(__FILE__))."/open/SvnOpen.class.php" );
require_once( dirname(dirname(__FILE__))."/plugins/validation/validation.inc.php" );

// getResourceType was only used from here, so we'll keep it here until it has found a nice home

/**
 * Tries a resource path in current HEAD, for the current user, returning status code.
 * @param $target the path in the current repository; accepts folder names without tailing slash.
 * @return 0 = does not exist, -1 = access denied, 1 = folder, 2 = file, boolean FALSE if undefined
 */
function login_getResourceType($target) {
	$url = getTargetUrl($target);
	if (substr_count($url, '://')!=1) trigger_error("The URL \"$url\" is invalid", E_USER_WARNING); // remove when not frequent error
	$request = new ServiceRequest($url);
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
		
		// try some incomplete cURL webdav write operation, getting a 403 but hopefluly not modifying if OK
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "SEARCH" ) ;

	}
}

// ---- presentation support ----

/**
 * Present the page with the results of all Edit->show calls,
 * where the last Edit's success status decides if the page should say error or done
 */
function presentEdit(&$presentation, $nextUrl=null, $headline=null, $summary=null) {
	if (!$nextUrl) {
		$nextUrl = dirname(getTargetUrl()); // get the parent folder for a file, and the folder itself for a folder
	}
	$presentation->assign('nexturl',$nextUrl);
	$presentation->assign('headline',$headline);
	$presentation->assign('summary',$summary);
	$presentation->enableRedirect();
	$presentation->display(dirname(__FILE__) . '/edit_done.html');
}

/**
 * Used if the current task should be aborted with an error message, and the status from last Edit->show.
 *
 * @param Smarty $presentation
 * @param String $errorMessage
 */
function presentEditAndExit(&$presentation, $nextUrl=null, $errorMessage=null) {
	if (!$errorMessage) $errorMessage = 'Versioning operation failed';
	presentEdit($presentation, $nextUrl, $errorMessage);
	exit;
}

// ---- the class ----

/**
 * The repository write operation class, representing an SVN operation and the result.
 * 
 * An action might consist of serveral Edit operation. Each operation can present the
 * results to the 'edit done' smarty template.
 * If the show() function is never called, this class does not need Presentation.class.php to be imported.
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
		return $this->operation;
	}
	
	/**
	 * @return string the log message if this is an operation that commits to the repository, null if not
	 */
	function getMessage() {
		if (!$this->commitWithMessage) return null;
		return $this->message;
	}
	
	/**
	 * Runs the operation securely (no risk for shell command injection)
	 */
	function exec() {
		if ($this->commitWithMessage) { // commands expecting a message need this even if it is empty
			$this->command->addArgOption('-m', $this->message);
		}
		return $this->command->exec();
	}
	
	/**
	 * @return true if operation completed successfuly
	 */
	function isSuccessful() {
		return $this->getExitcode() == 0;
	}
	
	function getExitcode() {
		return $this->command->getExitcode();
	}
	
	/**
	 * Resturns the last line of the command, which usually contains
	 * the conclusion, like "Committed revision 123"
	 * @return result of the subversion operation, empty string if it gave no output
	 */
	function getResult() {
		$o = $this->getOutput();
		if (count($o) > 0) return $o[count($o)-1];
		return '';
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
	 * @param Smarty $smartyTemplate a template that accepts 'assign'
	 * @param String $description a custom summary line for this operation, 
	 *  summary lines will always be visible, use \n as line break
	 */
	function show(&$smartyTemplate, $description=null) {
		$logEntry = array(
			'result' => $this->getResult(),
			'operation' => $this->getOperation(),
			'message' => $this->getMessage(),
			'successful' => $this->isSuccessful(),
			'revision' => $this->getCommittedRevision(),
			'output' => implode("\n", $this->getOutput())
		);
		$logEntry['description'] = $description;
		$smartyTemplate->append('log', $logEntry);
		
		// overwrite existing values, so that the last command decides the result
		$smartyTemplate->assign($logEntry);
	}
	
	/**
	 * Convenience method for versioning operations that rarely fail.
	 * Calls show, and then if this Edit is not successful it calls
	 * presentAndEdit with the default error message and nextUrl.
	 * Use this method to make sure that a complex task does not continue
	 * if a required step fails, but when there is no reason to write
	 * custom error handling for the step.
	 *
	 * @param Smarty $smartyTemplate a template that accepts 'assign'
	 * @param String $description a custom summary line for this operation, 
	 *  summary lines will always be visible, use \n as line break
	 */
	function showOrFail(&$smartyTemplate, $description=null) {
		$this->show($smartyTemplate, $description);
		if (!$this->isSuccessful()) {
			presentEditAndExit($smartyTemplate);
		}
	}
	
	/**
	 * Write the results of a single edit operation to a smarty template
	 * @param smarty initialized template engine
	 * @param nextUrl the absolute url to go to after the operation. Should be a folder in the repository. If null, referrer is used.
	 * @deprecated use Edit->show and presentEdit insead, which support multiple edit operations for a page
	 */
	function present(&$smarty, $nextUrl = null) {
		$this->show($smarty);
		presentEdit($smarty, $nextUrl);
	}

}
?>
