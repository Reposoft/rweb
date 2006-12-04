<?php

require("../lib/simpletest/setup.php");

// -- mock account ---
function targetLogin() {};
function isLoggedIn() {return true;};
function getReposUser() {return 'tst';};
function _getReposPass() {return 'pwd';};
function getHttpStatusFromHeader($str) {
	// for the OK test
	if (strContains($str, ' 200 OK')) return 200;
	// default
	return 'No mock behaviour for the header: '.$str;
};
// -----------
require("ServiceRequest.class.php");

// response for testing
if (isset($_GET[WEBSERVICE_KEY])) {
	header('HTTP/1.1 200 OK');
	echo '{"message":"test"';
	echo ',"user":"'.getReposUser().'"';
	echo ',"pass":"'._getReposPass().'"';
	echo '}';
	exit;
}

// tests
class TestServiceRequest extends UnitTestCase {
	
	function testGetUrl() {
		$service = new ServiceRequest('test/', array('a'=>'b'));
		$url = $service->getUrl();
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
		$processed = $service->headers;
		$this->assertEqual('HTTP/1.1 200 OK', $processed[0]);
		$this->assertEqual('Apache 2.0', $processed['Server']);
	}
	
	function testExec() {
		$service = new ServiceRequest('', array());
		$service->uri = repos_getSelfUrl();
		$service->exec();
		$this->assertEqual('{"message":"test","user":"tst","pass":"pwd"}', $service->getResponse());
		$this->assertTrue($service->isOK());
		$this->assertEqual(200, $service->getHttpStatus());
		$this->sendMessage($service->getResponseHeaders());
	}
	
}

testrun(new TestServiceRequest());

?>