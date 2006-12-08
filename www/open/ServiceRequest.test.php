<?php

// -- mock account ---
function targetLogin() {};
function isLoggedIn() {return true;};
function getReposUser() {return 'tst';};
function _getReposPass() {return 'pwd';};
// -----------


require("ServiceRequest.class.php");

// responses for testing
if (isset($_GET['redirect'])) {
	header('Location: '.repos_getSelfUrl());
	echo "redirecting";
	exit;
}
if (isset($_GET['useryes'])) {
	if (!isset($_SERVER['PHP_AUTH_USER'])) header('HTTP/1.1 401 Unauthorized');
	if (!isset($_SERVER['PHP_AUTH_PW'])) header('HTTP/1.1 401 Unauthorized');
	echo "user checked";
	exit;
}
if (isset($_GET['userno'])) {
	if (isset($_SERVER['PHP_AUTH_USER'])) header('HTTP/1.1 403 Forbidden');
	if (isset($_SERVER['PHP_AUTH_PW'])) header('HTTP/1.1 403 Forbidden');
	echo "user checked";
	exit;
}
if (isset($_GET[WEBSERVICE_KEY])) {
	header('HTTP/1.1 200 OK');
	echo '{"message":"test"';
	echo ',"user":"'.getReposUser().'"';
	echo ',"pass":"'._getReposPass().'"';
	echo '}';
	exit;
}
if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == SERVICEREQUEST_AGENT) {
	header('HTTP/1.1 412 Precondition Failed');
	echo 'The test service requires the service type paramter "'.WEBSERVICE_KEY.'"';
	exit; // prevent inifinite loop
}

// tests
require("../lib/simpletest/setup.php");

class TestServiceRequest extends UnitTestCase {
	
	function testGetUrl() {
		$service = new ServiceRequest('test/', array('a'=>'b'));
		$url = $service->_buildUrl();
		$this->assertEqual(getWebapp().'test/?a=b&'.WEBSERVICE_KEY.'='.$service->responseType,
			$url, "Should have built a GET url with an extra 'serv' parameter. %s");
	}
	
	function testProcessHeaders() {
		$service = new ServiceRequest('', array());
		// call once per header line
		$in = "HTTP/1.1 200 OK ";
		$bytes = $service->_processHeader(null, $in);
		$this->assertEqual(strlen($in), $bytes, "Should return the number of characters processed. %s");
		$in = "Server: Apache 2.0";
		$bytes = $service->_processHeader(null, $in);
		$this->assertEqual(strlen($in), $bytes, "Should return the number of characters processed. %s");
		// verify
		$processed = $service->getResponseHeaders();
		$this->assertEqual('HTTP/1.1 200 OK', $processed[0]);
		$this->assertEqual('Apache 2.0', $processed['Server']);
	}
	
	function testExec() {
		$service = new ServiceRequest('', array());
		$service->uri = repos_getSelfUrl();
		$service->exec();
		$this->assertEqual('{"message":"test","user":"tst","pass":"pwd"}', $service->getResponse());
		$this->assertTrue($service->isOK());
		$this->assertEqual(200, $service->getStatus());
		$this->sendMessage($service->getResponseHeaders());
	}
	
	function testExcecHeadersOnly() {
		$service = new ServiceRequest(repos_getSelfUrl(), array());
		$service->setSkipBody();
		$service->exec();
		$headers = $service->getResponseHeaders();
		$this->assertEqual('HTTP/1.1 200 OK', $headers[0]);
		$this->assertEqual('', $service->getResponseSize());
		$this->assertEqual(0, $service->getResponseSize());
		foreach ($headers as $h) {
			// check that there are no empty header entries
			$this->assertTrue(strlen(trim($h))>0);
		}
	}
	
	function testExcecNoService() {
		$service = new ServiceRequest(repos_getSelfUrl(), array());
		$service->setResponseTypeDefault();
		$this->sendMessage("This test hangs in an infinite loop if the internal user agent string is not set.");
		$service->exec();
		$this->assertEqual(412, $service->getStatus());
	}
	
	function testFollowRedirect() {
		// without redirect
		$service = new ServiceRequest(repos_getSelfUrl().'?redirect=1', array());
		$service->exec();
		$this->assertEqual(0, $service->getRedirectCount());
		$this->assertEqual(302, $service->getStatus());
		// enable redirect
		$service = new ServiceRequest(repos_getSelfUrl().'?redirect=1', array());
		$service->setFollowRedirects();
		$service->exec();
		$this->assertEqual(1, $service->getRedirectCount());
		$this->assertEqual(412, $service->getStatus());		
	}
	
	function testAuthentication() {
		$service = new ServiceRequest(repos_getSelfUrl().'?useryes', array());
		$service->exec();
		$this->assertEqual(200, $service->getStatus());
	}

	function testAuthenticationFalse() {
		$service = new ServiceRequest(repos_getSelfUrl().'?userno', array(), false);
		$service->exec();
		$this->assertEqual(200, $service->getStatus());
	}	
	
	/* this test is for a method that is no longer used
	function testgetHttpStatusFromHeader() {
		$this->assertEqual(200, getHttpStatusFromHeader("HTTP/1.1 200 OK"));
		$this->assertNotEqual(20, getHttpStatusFromHeader("HTTP/1.1 200 OK"));
		$this->assertNoErrors();
		$this->assertEqual("301", getHttpStatusFromHeader("HTTP/1.1 301 Moved Permanently"));
		$this->assertNoErrors();
		$this->assertEqual(401, getHttpStatusFromHeader("HTTP/1.1 401 Authorization Required"));
		$this->assertEqual("401", getHttpStatusFromHeader("HTTP/1.1 401 Authorization Required"));
		$this->assertNoErrors();
		$this->assertEqual(403, getHttpStatusFromHeader("HTTP/1.1 403 Forbidden"));
		$this->assertEqual("403", getHttpStatusFromHeader("HTTP/1.1 403 Forbidden"));
		$this->assertNoErrors();		
	}
	*/
	
}

testrun(new TestServiceRequest());

?>