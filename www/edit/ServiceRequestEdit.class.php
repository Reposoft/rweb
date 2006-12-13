<?php

if (!class_exists('ServiceRequest')) require (dirname(dirname(__FILE__)).'/open/ServiceRequest.class.php');

// define additional services
define('SERVICE_EDIT_COPY', 'edit/copy/');

/**
 * 
 */
class ServiceRequestEdit extends ServiceRequest {
	
	/**
	 * Reads the revision number from the response.
	 *
	 * @return int the revision number of the operation. We assume there is only one new revision per edit.
	 */
	function getCommittedRevision() {
		return 0;	
	}
}

?>