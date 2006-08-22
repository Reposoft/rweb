<?php
// common functionality in the edit tools
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));
require_once( PARENT_DIR."/account/login.inc.php" );

// the repository write operation class
class Edit {
	var $operation;
	var $arguments = new Array(); // command line arguments, not yet shellcmd-escaped
	var $message = '';

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
		$message = $commitMessage;
	}

	/**
	 * Replaces the current arguments array.
	 * Use addArgument instead if existing arguments should not be removed.
	 * @param arrArgumentsInOrder The arguments to the command ordered according to the svn reference
	 */
	function setArguments($arrArgumentsInOrder) {
		$argument = $arrArgumentsInOrder;
	}
	
	/**
	 * Append an command line argument last in the current arguments list
	 * @param
	 */
	function addArgument($nextArgument) {
		$arguments[] = $nextArgument;
	}
	
	/**
	 * Runs the operation securely (no risk for shell command injection)
	 */
	function execute() {
	
	}
	
	/**
	 * @return true if operation completed successfuly
	 */
	function worked() {
	
	}
	
	/**
	 * @return the output from the svn command
	 */
	function getResult() {
	
	}
	
	/**
	 * @return the revision number that this operation created upon success
	 */
	function getCommittedRevision() {
	
	}
}
?>