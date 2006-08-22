<?php
// common functionality in the edit tools


// the repository write operation class
class Edit {

	/**
	 * @param subversionOperation svn command line operation, for example mkdir or del
	 */
	function setCommand($subversionOperation) {
	
	}
	
	/**
	 * @param commitMessage The comments to save in svn log
	 */
	function setMessage($commitMessage) {
	
	}

	/**
	 * @param arrArgumentsInOrder The arguments to the command ordered according to the svn reference
	 */
	function setArguments($arrArgumentsInOrder) {
	
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