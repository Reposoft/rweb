<?php
// common functionality in the edit tools
require_once( dirname(rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR))."/account/login.inc.php" );

// Set HTTP output character encoding to UTF-8
mb_http_output('UTF-8');
// Start buffering and specify "mb_output_handler" as callback function
ob_start('mb_output_handler');

if (mb_http_input()==false) {
	trigger_error("Server setup error. Multibyte string HTTP input encoding is not supported.");
	exit;
}
if (mb_http_input()!="UTF-8") {
	trigger_error("Character encoding is '".mb_http_input()."', not 'UTF-8'");
	exit;
} 

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
		// rawurlencode does not work with filenames containing ���
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
		// urlEncodeNames does not work
		$this->_addArgument($url);
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
		$cmd = $this->escapeCommand($this->operation);
		if ($this->commitWithMessage) { // commands expecting a message need this even if it is empty
			$cmd .= ' -m '.$this->escapeArgument($this->message);
		}
		// arguments, enclosed in strings to allow spaces
		foreach ($this->args as $arg) {
			$cmd .= ' '.$this->escapeArgument($arg);
		}
		return $cmd;
	}
	
	// Commands are the first element on the command line and can not be enclosed in quotes
	function escapeCommand($command) {
		return escapeshellcmd($command);
	}
	
	// Encloses an argument in quotes and escapes any quotes within it
	function escapeArgument($argument) {
		return '"'.str_replace('"', '\\"', $argument).'"';
	}
	
	/**
	 * Runs the operation securely (no risk for shell command injection)
	 */
	function execute() {	
		$cmd = login_getSvnSwitches().' '.$this->getCommand();
		$this->output = repos_runCommand('svn', $cmd);
		$this->returnval = array_pop($this->output);
		$this->result = $this->output[count($this->output)-1];
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
