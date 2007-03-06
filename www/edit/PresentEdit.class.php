<?php
/**
 * Handles presentation of operations that update the repository.
 *
 * @package edit
 */
require(dirname(dirname(__FILE__)).'/conf/Presentation.class.php');
 
$editStart = null;
$editSteps = array();

class PresentEditStart {
	
	function PresentEditStart() {
		// from now on we will complete the operation even if browser disconnects
		ignore_user_abort();
		
	}
	
	function display() {
		
	}
	
}

class PresentEditStep {
	
	/**
	 * Enter description here...
	 *
	 * @param SvnEdit $executedSvnEdit
	 *  TODO handle ServiceRequestEdit
	 * @return PresentEditStep
	 */
	function PresentEditStep($executedSvnEdit) {
		
	}

	function display() {
		
	}	
	
}

class PresentEditStop {
	
	function PresentEditStop() {
		
	}
	
	function display() {
		
	}
}


?>
