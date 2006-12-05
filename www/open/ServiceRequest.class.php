<?php
/**
 * Calls an internal service synchronously and returns the reponse as XML, JSON or text.
 * 
 * It is assumed that services require login, so login.inc.php is always included,
 * but it will login only if 'target' is set (see the targetLogin concept) or if
 * the service says authorization required.
 * 
 * @package Edit
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */

if (!function_exists('targetLogin')) require(dirname(dirname(__FILE__)).'/account/login.inc.php');

if (!function_exists('curl_init')) trigger_error('Service calls require the PHP "curl" extension');

define('SERVICE_LOG', 'open/log/');

/**
 * Does a web service GET request.
 * Authenticates as the logged in user for the call, if needed.
 * The response page can call isRequestService() to identify
 * a request from this method.
 * 
 * Same as jquery, $.get("test.cgi", { name: "John", time: "2pm" })
 * @param String $uri the resource from server root, starting with slash
 * @param String $jsonParams the parameters to the request, as a JSON string
 * @param String $host optional, if empty the current host will be used
 * @return String the reponse, or an integer HTTP status code on error
 */
function _deprecated_requestService($uri, $jsonParams, $host='') {
	// TODO add method that does not require URI or host, but only the service path in the webapp
	// TODO probably it is better to use an array of params
	// TODO add serv=1
}
// using a class instead
class ServiceRequest {
	
	var $uri;
	var $parameters;
	var $responseType = 'json'; // "xml", "html", or "json"
	
	// response storage
	var $headers = array();
	var $response;
	
	/**
	 * Creates the model of a GET request.
	 *
	 * @param String $service the intenal service URI, for example "open/log/", use constants
	 * @param array $parameters [String] query parameters as associative array, _not_ urlencoded
	 * @return ServiceRequest which might be further configured with set* methods
	 */
	function ServiceRequest($service, $parameters) {
		$this->uri = getWebapp().$service;
		$this->parameters = $parameters;
	}
	
	/**
	 * Launches the request, synchronously, and returns this instance when done.
	 *
	 * @return ServiceRequest this instance, meaning that the response is received
	 */
	function exec() {
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $this->getUrl()); 
		// set callbacks
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this, '_processHeader'));
		// temporary authentication solution
		if (isLoggedIn()) {
			curl_setopt($ch, CURLOPT_USERPWD, rawurlencode(getReposUser()).':'.rawurlencode(_getReposPass()));  
		}
		// run the request
		$this->response = curl_exec($ch);
		return $this;
	}
	
	/**
	 * Builds the url with the parameters for a GET request,
	 * appending [WEBSERVICE_KEY]=[responseType]
	 */
	function getUrl() {
		$url = $this->uri . '?';
		foreach ($this->parameters as $key => $value) {
			$url .= $key.'='.rawurlencode($value).'&';
		}
		$url .= WEBSERVICE_KEY.'='.$this->responseType;
		return $url;
	}
	
	/**
	 * cURL callback function for handling header lines received in the response
	 *
	 * @param unknown_type $ch CURL resource
	 * @param unknown_type $header string with the header data to be written
	 * @return int the number of bytes written
	 */
	function _processHeader($ch, $header){
		if (($p=strpos($header,':'))===false) {
			$this->headers[] = trim($header);
		} else {
			$this->headers[substr($header, 0, $p)] = trim(substr($header, $p+1));
		}
		return strlen($header);
   }
	
   /**
	* Returns the HTTP headers
	*
	* @return array [String] the response headers as an associative array
	*  where element [0] is the status and the others are 'Server'=>'Apache 2'
	*/
   function getResponseHeaders() {
   	return $this->headers;
   }
   
	/**
	 * @return int The HTTP status code of the reponse, for example 200 for success.
	 *  "412 Precondition Failed" on validation error for the give parameters.
	 *	 "500 Internal Server Error" if the service generated an error
	 */
	function getHttpStatus() {
		return getHttpStatusFromHeader($this->headers[0]);
	}
	
	/**
	 * @return boolean true if HTTP status is 200
	 */
	function isOK() {
		return ($this->getHttpStatus()==200);
	}
	
	/**
	 * Returns the response body of the service call.
	 * 
	 */
	function getResponse() {
		return $this->response;
	}
	
}

?>