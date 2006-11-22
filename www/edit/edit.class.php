<?php
// common functionality in the edit tools
require_once( dirname(dirname(__FILE__))."/account/login.inc.php" );
require_once( dirname(dirname(__FILE__))."/plugins/validation/validation.inc.php" );

// shared validation rule
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

// the repository write operation class
class Edit {
	var $operation;
	var $args = Array(); // command line arguments, properly escaped and surrounded with quotes if needed
	var $message; // not escaped
	var $commitWithMessage = false; // allow for example checkout to run without a -m
	var $result;
	var $output; // from exec
	var $returnval; // from exec

	/**
	 * Constructor
	 * @param subversionOperation svn command line operation, for example mkdir or del
	 */
	function Edit($subversionOperation) {
		$this->operation = $subversionOperation;
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
		// rawurlencode does not work with filenames because there might be UTF-8 characters in it like umlauts
		// manually escape the characters that are allowed in filenames but not for retreival
		$this->_addArgument(escapeArgument($pathElement));
	}
	
	/**
	 * @param name absolute or relative path with slashes
	 *  Path is expected to be properly adapted to the OS already (see toPath and toShellEncoding)
	 */
	function addArgPath($name) {
		$this->_addArgument(escapeArgument($name));
	}

	/**
	 * @param url complete url
	 */	
	function addArgUrl($url) {
		// urlEncodeNames does not work for write operation because there might be UTF-8 characters like umlauts
		$url = urlEncodeNames($url);
		$this->_addArgument(escapeArgument($url));
	}
	
	/**
	 * @param option command line switch, will be added to commandline without encoding or quotes
	 */	
	function addArgOption($option, $safe=false) {
		$this->_addArgument($option);
	}

	/**
	 * Append an command line argument last in the current arguments list
	 * @param The argument, should be appropriately encoded
	 *  (for example urlencoding for a new filename from input box)
	 */
	function _addArgument($nextArgument) {
		$this->args[] = $nextArgument;
	}

	/**
	 * Replaces the current arguments array.
	 * Use addArgument instead if existing arguments should not be removed.
	 * @param arrArgumentsInOrder The arguments to the command ordered according to the svn reference
	 */
	function _setArguments($arrArgumentsInOrder) {
		$this->args = $arrArgumentsInOrder;
	}
	
	/**
	 * @return the subversion operation command line (the portion after 'svn')
	 */
	function getCommand() {
		// command and message
		$cmd = escapeCommand($this->operation);
		if ($this->commitWithMessage) { // commands expecting a message need this even if it is empty
			$cmd .= ' -m '.escapeArgument($this->message);
		}
		// arguments, already encoded
		foreach ($this->args as $arg) {
			$cmd .= ' '.$arg;
		}
		return $cmd;
	}
	
	/**
	 * Runs the operation securely (no risk for shell command injection)
	 */
	function execute() {
		$cmd = login_getSvnSwitches().' '.$this->getCommand();
		$this->output = repos_runCommand('svn', $cmd);
		$this->returnval = array_pop($this->output);
		if (count($this->output) > 0) {
			$this->result = $this->output[count($this->output)-1];
		}
	}
	
	/**
	 * @return true if operation completed successfuly
	 */
	function isSuccessful() {
		return $this->returnval == 0;
	}
	
	/**
	 * @return the output from the svn command
	 */
	function getResult() {
		return $this->result;
	}
	
	/**
	 * @return the output from the svn command
	 */
	function getOutput() {
		return $this->output;
	}
	
	/**
	 * @return the revision number that this operation created upon success
	 */
	function getCommittedRevision() {
		if ($this->isSuccessful()) {
			$match = ereg('^[a-zA-Z ]+([0-9]+)', $this->result, $rev);
			return $rev[1];
		}
		return null;
	}
	
	/**
	 * Write the results to a smarty template
	 * @param smarty initialized template engine
	 * @param nextUrl the url to go to after the operation. Should be a folder in the repository. If null, referrer is used.
	 */
	function present($smarty, $nextUrl = null) {
		if (!$nextUrl) {
			$nextUrl = dirname(getTargetUrl()); // parent only if it's a file
		}
		if (strlen($this->getResult()) > 0) {
			$smarty->assign('result', $this->getResult());
		} else {
			$smarty->assign('result', 'Error. Could not read result for the command: ' . $this->getCommand());
		}
		$smarty->assign('nexturl',$nextUrl);
		$smarty->assign('operation',$this->operation);
		$smarty->assign('revision',$this->getCommittedRevision());
		$smarty->assign('successful',$this->isSuccessful());
		if (!$this->isSuccessful()) {
			$smarty->assign('output',implode('<br />', $this->output));
		}
		$smarty->enableRedirect();
		$smarty->display($this->getDoneTemplate());
	}
	
	function getDoneTemplate() {
		return dirname(__FILE__) . '/edit_done.html';
	}

}
?>
