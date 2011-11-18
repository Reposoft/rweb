<?php
/**
 * This test must run in a web server because it makes requests to itself.
 */

// -- mock account ---
function targetLogin() {};
function isLoggedIn() {return true;};
$test_user = 'Test User';
function getReposUser() {global $test_user; return $test_user;};
function _getReposPass() {return 'pwd';};
// -----------

require("ServiceRequest.class.php");

// responses for testing
if (isset($_GET['redirect'])) {
	header('Location: '.getSelfUrl());
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
if (isset($_GET['lockno'])) {
	// curl -I http://localhost:8530/svn/repo1/demo/repos.html -X LOCK -H "If-Match: shouldnevermatch"
	header('WWW-Authenticate: Basic realm="repos test multirepo"');
	header('HTTP/1.1 401 Authorization Required'); // must be set after WWW-Authenticate because PHP sets 401 by default
	exit;
}
// used for testExec, watch out so we don't 
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
		$_SERVER['REPOS_HOST'] = 'http://example.com';
		$service = new ServiceRequest('test/', array('a'=>'b'));
		$url = $service->_buildUrl();
		$this->assertEqual('http://example.com/repos-web/test/?a=b&'.WEBSERVICE_KEY.'='.$service->responseType,
			$url, "Should have built a GET url with an extra 'serv' parameter. %s");
		unset($_SERVER['REPOS_HOST']);
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
	
	function testBuildUrlService() {
		$s = new ServiceRequest('', array());
		$s->uri = 'open/json/';
		$url = $s->_buildUrl();
		$this->assertNotEqual('open/json/',$url);
		$this->assertTrue(strstr($url, getWebapp()), 'Should have added webapp to the service uri');
		$this->assertTrue(strpos($url, '://'), 'Must contain a hostname even if webapp is a path: $url. %s');
	}

	function testBuildUrlPath() {
		$s = new ServiceRequest('', array());
		$s->uri = '/repos-admin/config/hooks/';
		$url = $s->_buildUrl();
		$this->assertNotEqual('/repos-admin/config/hooks/',$url);
		$this->assertTrue(strstr($url, getHost().'/repos-admin/config/hooks/'), "Should have added host, got: $url. %s");
	}
		
	function testForUrl() {
		$s = ServiceRequest::forUrl('http://host.se/a/b.php?c=d&e=f');
		$this->assertEqual('http://host.se/a/b.php', $s->uri);
		$this->assertEqual(2, count($s->parameters));
		$this->assertEqual('f', $s->parameters['e']);
	}

	// ------- The tests below here use exec() without any mocks, so they are really integration tests --------		
	
	function testExec() {
		$service = new ServiceRequest('', array());
		$service->uri = getSelfUrl();
		$this->sendMessage($service->_buildUrl());
		$service->exec();
		$this->assertEqual('{"message":"test","user":"Test User","pass":"pwd"}', $service->getResponse());
		$this->assertTrue($service->isOK());
		$this->assertEqual(200, $service->getStatus());
		$this->sendMessage($service->getResponseHeaders());
	}
	
	function testExcecHeadersOnly() {
		$service = new ServiceRequest(getSelfUrl(), array());
		$service->setSkipBody();
		$this->sendMessage($service->_buildUrl());
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
	
	function testExcecAbsolutePath() {
		$path = substr(getSelfUrl(), strlen(getHost()));
		$this->sendMessage("Using url without host: $path");
		$service = new ServiceRequest(getSelfUrl(), array());
		
		$this->sendMessage($service->_buildUrl());
		$service->exec();
		$headers = $service->getResponseHeaders();
		$this->assertEqual('HTTP/1.1 200 OK', $headers[0]);
	}	
	
	function testExcecNoService() {
		$service = new ServiceRequest(getSelfUrl(), array());
		$service->setResponseTypeDefault();
		$this->sendMessage("This test hangs in an infinite loop if the internal user agent string is not set.");
		// or does it? maybe it nowadays sets the serv=json parameter anyway and that is what's detected?
		$service->exec();
		$this->assertEqual(412, $service->getStatus());
	}
	
	function testFollowRedirect() {
		// get redirect page but don't follow redirect
		$service = new ServiceRequest(getSelfUrl(), array('redirect'=>'1'));
		$service->exec();
		$this->sendMessage('This test expects a 302 and Location header for encoded URL: '.$service->_buildUrl());
		$this->assertEqual(0, $service->getRedirectCount());
		$this->assertEqual(302, $service->getStatus());
		$this->sendMessage($service->getResponseHeaders());
		$this->sendMessage($service->getResponse());
		// enable redirect
		$service = new ServiceRequest(getSelfUrl(), array('redirect'=>'1'));
		$service->setFollowRedirects();
		$service->exec();
		$this->assertEqual(1, $service->getRedirectCount());
		$this->assertEqual(412, $service->getStatus());
		$this->sendMessage($service->getResponseHeaders());
		$this->sendMessage($service->getResponse());
	}
	
	function testAuthentication() {
		$service = new ServiceRequest(getSelfUrl(), array('useryes'=>''));
		// test the internal logic for authentication
		if (isLoggedIn()) {
			$this->assertEqual(getReposUser(), $service->_username, 'Authentication=true -> username should be set');
		} else {
			$this->assertTrue(false===$service->_username, 'Authentication=true without login -> username should be ===false');
		}
		// test the effects
		$service->exec();
		$this->assertEqual(200, $service->getStatus());
	}

	function testAuthenticationExplicitlyDisabled() {
		$service = new ServiceRequest(getSelfUrl(), array('userno'=>''), false);
		$this->assertEqual(null, $service->_username, 'Explicitly disable authentication -> username should be null');
		$this->assertTrue($service->isAuthenticationDisabled());
		$service->exec();
		$this->assertEqual(200, $service->getStatus());
	}
	
	function testAuthenticationEmptyUsername() {
		global $test_user;
		$test_user = '';
		$service = new ServiceRequest(getSelfUrl(), array('useryes'=>''));
		$this->expectError('Unexpected login status, username is empty.');
		$service->exec();
		$test_user = 'Test User';
	}
	
	function testAuthenticationStatus403WithAuthHeader() {
		$service = new ServiceRequest(getSelfUrl(), array('lockno'=>''), true);
		// The test can't set alternate HTTP method like _svnFileIsWritable does.
		// The test won't get a redirect unless output_buffering is on.
		$this->expectError('Could not request authentication because output had already started.');
		$service->exec();
	}
	
}

testrun(new TestServiceRequest());

?>
