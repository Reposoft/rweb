<?php
/**
 * Web service commit calls (c) 2007 Staffan Olsson www.repos.se
 */
if (!class_exists('ServiceRequest')) require (dirname(dirname(__FILE__)).'/open/ServiceRequest.class.php');
if (!class_exists('ServiceRequest')) require (dirname(dirname(__FILE__)).'/lib/json/json.php');

// define additional services
define('SERVICE_EDIT_COPY', 'edit/copy/');
define('SERVICE_EDIT_PROPSET', 'edit/propset/');

/**
 * ServiceRequest that expects JSON response and returns revision number from it.
 */
class ServiceRequestEdit extends ServiceRequest {

	function __construct($service, $parameters=array(), $authenticate=true) {
		parent::__construct($service, $parameters, $authenticate);
		$this->setCustomHttpMethod('POST'); // all operation that edit data should be POST according to HTTP spec
	}
	
	function getResponseJsonToArray() {
		$response = $this->getResponse();
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		return $json->decode($response);
	}
	
	/**
	 * Reads the revision number from the response.
	 *
	 * @return int the revision number of the operation. We assume there is only one new revision per edit.
	 */
	function getCommittedRevision() {
		$data = $this->getResponseJsonToArray();
		if (isset($data['error'])) trigger_error($data['error'], E_USER_ERROR);
		$lastOperation = $data['operation'];
		$lastResult = $data['result'];
		$lastRevision = $data['revision'];
		// display like SvnEdit
		if (class_exists('Presentation')) {
			$smartyTemplate = Presentation::getInstance();
			$logEntry = array(
				'result' => $lastResult,
				'operation' => $this->uri,
				'message' => $lastResult,
				'successful' => $this->isOK(),
				'revision' => $lastRevision,
				'output' => $this->getResponse(),
				'description' => ''
			);
			$smartyTemplate->append('log', $logEntry);
			// overwrite existing values, so that the last command decides the result
			$smartyTemplate->assign($logEntry);
		}
		// see also SvnEdit::getCommittedRevision
		$expect = '/Committed \w+sion (\d+)/';
		if (preg_match($expect, $lastResult, $matches)) {
			if ($matches[1] == $lastRevision) return $matches[1];
			trigger_error('Unexpected result "'.$lastResult.'", should be revision '.$lastRevision, E_USER_ERROR);
		} else {
			trigger_error($this->uri.' did not commit a new revision. Result was: '.$lastResult, E_USER_ERROR);
		}
	}
}

?>