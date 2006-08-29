<?php
// common functionality in the edit tools
require_once( dirname(rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR))."/account/login.inc.php" );

// TODO need a generic solution to avoid double POST on refresh

// the repository write operation class
class Edit {
	var $operation;
	var $args = Array(); // command line arguments, not yet shellcmd-escaped
	var $message;
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
	 * @param commitMessage The comments to save in svn log
	 */
	function setMessage($commitMessage) {
		$this->message = $commitMessage;
		$this->commitWithMessage = true;
	}

	// different addArgument functions to be able to adapt encoding

	/**
	 * @param pathElement filename or directory name
	 * @param safe true if there is no way the value can be modified by the user
	 */
	function addArgFilename($pathElement, $safe=false) {
		// rawurlencode does not work with filenames containing едц
		$this->_addArgument($pathElement);
	}
	
	/**
	 * @param name absolute or relative path with slashes
	 * @param safe true if there is no way the value can be modified by the user
	 */
	function addArgPath($name, $safe=false) {
		$this->_addArgument($name);
	}

	/**
	 * @param url complete url
	 * @param safe true if there is no way the value can be modified by the user
	 */	
	function addArgUrl($url, $safe=false) {
		$this->_addArgument(urlEncodeNames($url));
	}
	
	/**
	 * @param option command line switch
	 * @param safe true if there is no way the value can be modified by the user
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
		$this->args[count($this->args)] = $nextArgument;
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
		array_walk($this->args, 'escapeshellcmd');
		$cmd = escapeshellcmd($this->operation) . ' ';
		if ($this->commitWithMessage) { // commands expecting a message need this even if it is empty
			$cmd .= '-m "'.escapeshellcmd($this->message).'" ';
		}
		$cmd .= implode(' ', $this->args); // TODO escape shellcmd for args
		return $cmd;
	}
	
	/**
	 * Runs the operation securely (no risk for shell command injection)
	 */
	function execute() {
		$cmd = getSvnCommand() . $this->getCommand();
		// execute with 2>&1 to get errors into the output array
		$this->result = exec("$cmd 2>&1", $this->output, $this->returnval);
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
			$nextUrl = $smarty->get_template_vars('referer');
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
