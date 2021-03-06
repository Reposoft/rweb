<?php
/**
 * Web service interface (c) 2006-2007 Staffan Olsson www.repos.se
 * 
 * Calls an internal service synchronously and returns the reponse as XML, JSON, HTML or text.
 * 
 * It is assumed that services require login, so login.inc.php is always included,
 * but it will login only if 'target' is set (see the targetLogin concept) or if
 * the service says authorization required.
 * 
 * @package open
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */

if (!function_exists('curl_init')) trigger_error('Service calls require the PHP "curl" extension');
// not dependent on the System class, this is only web functions

/**
 * The User-Agent: header contents for internal requests
 */
define('SERVICEREQUEST_AGENT', 'Repos service request');
define('SERVICEREQUEST_MAX_REDIRECTS', 10);

/**
 * Identify Repos as proxy.
 * Note that this applies to HTTP requests only, not proxied svn client operations.
 */
define('SERVICEREQUEST_CLIENT_IP_HEADER', 'X-Forwarded-For');

/**
 * responseType values
 */
define('SERVICE_TYPE_HTML', 'html');
define('SERVICE_TYPE_XML', 'xml');
define('SERVICE_TYPE_TEXT', 'text');
define('SERVICE_TYPE_JSON', 'json');

/**
 * Predefined services
 */
define('SERVICE_LOG', 'open/log/');
// TODO someone might be surprised when repos-admin is hard coded here, used from repos-admin/account/create/
define('SERVICE_ACL', '/repos-admin/account/acl/');
/**
 * Protected services that can be called internally for any client.
 * TODO not valid in repos 1.2
 */
define('SERVICE_PUBLIC_LICENSE', 'admin/license/');
define('SERVICE_PUBLIC_SERVER', 'admin/aboutserver/');
define('SERVICE_PUBLIC_CLIENT', 'admin/aboutclient/');
define('SERVICE_PUBLIC_STATUS', 'admin/');

if (!function_exists('getWebapp')) require(dirname(dirname(__FILE__)).'/conf/repos.properties.php');

/**
 * Login functionality is only included if needed 
 */
function _servicerequest_include_login() {
	if (!function_exists('isLoggedIn')) require(dirname(dirname(__FILE__)).'/account/login.inc.php');
}

/**
 * Encapsulates an http request as a synchronous stateful operation.
 * 
 * The get* and is* functions of the instance can be used only after exec().
 */
class ServiceRequest {
	
	// mandatory parameters
	var $uri;
	var $parameters;
	var $responseType = SERVICE_TYPE_JSON;
	
	// optional login: if username is_null login will not be done (any 401 status returned)
	// if username is ===false basic authentication will be detected and forwarded
	var $_username = null;
	var $_password = '';
	
	// option flags changed with set* functions
	var $followRedirects = false;
	var $skipBody = false;
	var $customMethod = null;
	var $customHeaders = array();
	
	// response storage
	var $headers = array();
	var $response;
	var $info;
	
	/**
	 * Creates the model of a GET request.
	 *
	 * @param String $service the intenal service URI, for example "open/log/", _not_ urlencoded.
	 *  Use SERVICE_ constants for the repos services.
	 *  If service starts with '/', or contains '://' it is cosidered an absolute URL instead.
	 * @param array $parameters [String] query parameters as associative array, _not_ urlencoded
	 * @param boolean $authenticate If false, never authenticate. Can be used to check if a resource
	 *  requires authentication. If true, reuse current HTTP authentication for the request.
	 *  If authenticate=true but there is no logged in user, the exec() method will attempt
	 *  to detect Authorization Required header from the service, forward those headers and exit.
	 * @return ServiceRequest which might be further configured with set* methods
	 */
	function __construct($service, $parameters=array(), $authenticate=true) {
		$this->uri = $service;
		$this->parameters = $parameters;
		// get client ip and set header according to constant
		$this->setRequestHeader(SERVICEREQUEST_CLIENT_IP_HEADER, $_SERVER['REMOTE_ADDR']);
		// auth proxy
		if ($authenticate) $this->_enableAuthentication();
	}
	
	/**
	 * Instantiates a ServiceRequest from an URL with query string
	 * Example: <code>$s = ServiceRequest::forUrl('http://host/?a=b&c=d');</code>
	 * @param String $url complete url with query string, not encoded
	 * @return ServiceRequest after calling constructor
	 * @static 
	 */
	function forUrl($url, $authenticate=true) {
		$param = array();
		if ($q = strpos($url, '?')) {
			$p = explode('&', substr($url, $q+1));
			foreach($p as $pa) {
				list($key, $value) = explode('=', $pa);
				$param[$key] = $value;
			}
			$url = substr($url, 0, $q);
		}
		return new ServiceRequest($url, $param, $authenticate);
	}
	
	/**
	 * Enables authentication in the request, with the same user that is authenticated now.
	 * If no user is authenticated, read the realm from the service URL, and return login box.
	 */
	function _enableAuthentication() {
		_servicerequest_include_login();
		if (isLoggedIn()) {
			$this->_username = getReposUser();
			$this->_password = _getReposPass();
		} else {
			$this->_username = false;
		}
	}
	
	/**
	 * Overrides the default parameter for service request, which is 'json'.
	 *
	 * @param String $service the expected response format,  "xml", "html", "text" or "json".
	 * @return ServiceRequest this instance
	 */
	function setResponseType($service) {
		$this->responseType = $service;
		return $this;
	}
	
	/**
	 * Makes a request without specifying WEBSERVICE_KEY, 
	 * thus requesting the same response that a user's browser would get.
	 *
	 * @return ServiceRequest this instance
	 */
	function setResponseTypeDefault() {
		$this->responseType = null;
		return $this;
	}
	
	/**
	 * Allows the request to follow Location headers. Default is to return the headers directly, even for Location.
	 * A maximum of 100 redirects will be set.
	 * @return ServiceRequest this instance
	 */
	function setFollowRedirects() {
		$this->followRedirects = true;
		return $this;
	}
	
	/**
	 * Allows skipping the retreival of response body, which saves some time when only checking headers.
	 * @return ServiceRequest this instance
	 */
	function setSkipBody() {
		$this->skipBody = true;
		return $this;
	}
	
	/**
	 * Allow a diffrent method than GET
	 * @param String $method uppdercase POST, PROPGET, PUT, CHECKOUT ...
	 */
	function setCustomHttpMethod($method) {
		$this->customMethod = $method;
		return $this;
	}
	
	/**
	 * 
	 */
	function setRequestHeader($name, $value) {
		$this->customHeaders[] = $name.': '.$value;
		return $this;
	}
	
	function isAuthenticationDisabled() {
		return is_null($this->_username);
	}
	
	/**
	 * Launches the request, synchronously, and returns this instance when done.
	 * The get* and is* functions of the instance can be used only after exec().
	 *
	 * @return int the http status code of the response
	 */
	function exec() {
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_USERAGENT, SERVICEREQUEST_AGENT); // required to detect loops
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $this->_buildUrl());
		// set callbacks
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this, '_processHeader'));
		// custom options
		$this->_customize($ch);
		// run the request
		$this->response = curl_exec($ch);
		$this->info = curl_getinfo($ch);
		curl_close($ch);
		// proxy basic authentication unless authentication was explicitly disabled in constructor
		if (!$this->isAuthenticationDisabled() && $this->getStatus()==401) {
			$this->_forwardAuthentication($this->getResponseHeaders());
		}
		return $this->getStatus();
	}
	
	/**
	 * Writes authentication headers and error page to the response and exits current request.
	 * The result of this method should be quite close to SvnOpen->handleAuthenticationError
	 * @param unknown_type $headers service response headers,
	 * 	those relevant to authentication can be forwarded to the client.
	 */
	function _forwardAuthentication($headers) {
		// don't want post request to be resent. Authentication should really have been taken care of when form was shown.
		// Same principle as in SvnOpen->handleAuthenticationError
		if ($_SERVER['REQUEST_METHOD'] == 'POST') trigger_error('Client should have been authenticated before submit.');
		// show a page in case the user cancels login
		if (!headers_sent()) {
			header($headers[0]);
			header('WWW-Authenticate: '.$headers['WWW-Authenticate']);
			// this is a bit different from SvnOpen which uses trigger_error, here we require html page
			if (class_exists('Presentation')) {
				$p = new Presentation();
				// this call can may not send a new status header, because we need the 401
				$p->showErrorNoRedirect('This service requires authentication', 'Authentication Required');
			} else {
				// handle missing authentication when service is called in a non-GUI context
				// this call can may not send a new status header, because we need the 401
				echo "This service requires authentication";
			}
			// browser will retry the same request with authentication
			exit;
		} else {
			trigger_error("Could not request authentication because output had already started.", E_USER_ERROR);
		}
	}
	
	/**
	 * Customize the cURL request after mandatory options have been set
	 *
	 * @param resource $ch the current cURL instance handle
	 */
	function _customize($ch) {
		if ($this->customMethod) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->customMethod);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->customHeaders);
		if ($this->followRedirects) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, SERVICEREQUEST_MAX_REDIRECTS);
		} else {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		}
		if ($this->skipBody) {
			curl_setopt($ch, CURLOPT_NOBODY, true);
		}
		// authentication, null means do not authenticate, false means user not authenticated yet
		if (!$this->isAuthenticationDisabled() && $this->_username!==false) {
			if ($this->_username === '') trigger_error('Unexpected login status, username is empty.', E_USER_ERROR);
			curl_setopt($ch, CURLOPT_USERPWD, 
				$this->_username.':'.$this->_password);
		}
		// set preferred response language
		curl_setopt($ch, CURLOPT_COOKIE, LOCALE_KEY.'=en');
		// allow SSL connection without checking CA
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	}
	
	/**
	 * Builds the url with the parameters for a GET request,
	 * appending [WEBSERVICE_KEY]=[responseType]
	 */
	function _buildUrl() {
		$url = urlEncodeNames($this->uri); // curl does not encode
		if (substr($url,0,1)=='/') $url = getHost().$url;
		if (!strpos($url,'://')) $url = getWebappUrl().$url;
		$url .= strpos($url, '?') ? '&' : '?';
		foreach ($this->parameters as $key => $value) {
			$url .= $key.'='.rawurlencode($value).'&';
		}
		if (!is_null($this->responseType)) {
			$url .= WEBSERVICE_KEY.'='.$this->responseType;
		}
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
		if (strlen(trim($header))==0) return strlen($header); // seems we get an empty header last
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
	function getStatus() {
		return $this->info['http_code'];
	}
	
	/**
	 * @return boolean true if HTTP status is 200
	 */
	function isOK() {
		return ($this->getStatus()==200);
	}
	
	/**
	 * @return String the response body of the service call.
	 */
	function getResponse() {
		return $this->response;
	}
	
	/**
	 * @return The complete URL of the response,
	 *  which might be different than the original URL if redirects are allowed.
	 */
	function getResponseUrl() {
		return $this->info['url'];
	}
	
	/**
	 * @return String the contents of the Content-Type header, with or without charset part.
	 */
	function getResponseType() {
		return $this->info['content_type'];
	}
	
	/**
	 * @return int The size of the response body in bytes.
	 */
	function getResponseSize() {
		return $this->info['size_download'];
	}
	
	/**
	 * @return float The total time for request and response transfer in seconds.
	 */
	function getResponseTime() {
		return $this->info['total_time'];
	}
	
	/**
	 * @return int the number of redirects due to 301 or 302 status.
	 */
	function getRedirectCount() {
		return $this->info['redirect_count'];
	}
	
}

?>
