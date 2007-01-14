<?php
require('login.inc.php');
require("../lib/simpletest/setup.php");

define('TESTHOST', repos_getSelfRoot());
define('TESTREPO', TESTHOST."/testrepo");
//define('TESTREPO', "http://test.repos.se/testrepo/");
//define('TESTREPO', "http://alto.optime.se/testrepo/");
 
class Login_include_Test extends UnitTestCase {
	
	function setUp() {
		// these tests should not rely on a logged in browser
		//testGetResourceType needs auth currently
		//unset($_SERVER['PHP_AUTH_USER']);
		//unset($_SERVER['PHP_AUTH_PW']);
	}
	
	function testGetReposUserEncode() {
		$_SERVER['PHP_AUTH_USER'] = "A B";
		$this->assertEqual("A B", getReposUser(), "Username should not be encoded");
		$_SERVER['PHP_AUTH_USER'] = "A+B";
		$this->assertEqual("A+B", getReposUser());
		unset($_SERVER['PHP_AUTH_USER']);
	}

	function testGetAuthNameReturnsSomething() {
		$url = TESTREPO;
		$realm = getAuthName($url);
		$this->assertTrue(strlen($realm)>0);
	}

	function testGetAuthNameReturnsRepositoryRootUrl() {
		$url = TESTREPO;
		$realm = getAuthName($url);
		$this->assertTrue(TESTREPO, $realm);
	}	
	
	// this belongs to a system configuration test
	function testGetAuthNameNoAuthAtServerRoot() {
		$url = repos_getSelfRoot().'/';
		$realm = getAuthName($url);
		$this->assertEqual(false, $realm);
	}
	
	// composition of target url (absolute URI)
	function testTargetUrl() {
		$_REQUEST[REPO_KEY] = 'http://my.repo';
		$_REQUEST['target'] = '/my/dir/file.txt';
		$this->assertEqual('http://my.repo/my/dir/file.txt', getTargetUrl());
		unset($_REQUEST[REPO_KEY]);
		unset($_REQUEST['target']);
	}
	
	function testTargetUrlFromPath() {
		$_REQUEST[REPO_KEY] = 'http://my.repo';
		$this->assertEqual('http://my.repo/h.txt', getTargetUrl('/h.txt'));
		unset($_REQUEST[REPO_KEY]);
	}
	
	function testTargetUrldecode() {
		
		//"https%3A%2F%2Fwww.repos.se%2Fsweden%2Fsvensson%2Ftrunk%2F"
	}
	
	// getTargetUrl should throw error if there is no target
	function testGetTargetUrlFalse() {
		$this->expectError();
		$this->expectError();
		getTargetUrl();
	}
	
	// url manipulation for the logged in user
	function testGetLoginUrl() {
		$_SERVER['PHP_AUTH_USER'] = 'mE';
		$_SERVER['PHP_AUTH_PW'] = 'm&p8ss';
		$url = _getLoginUrl('https://my.repo:88/home');
		$this->assertEqual('https://mE:m&p8ss@my.repo:88/home', $url);
		unset($_SERVER['PHP_AUTH_USER']);
		unset($_SERVER['PHP_AUTH_PW']);
	}
	
	function testGetLoginUrlNoUser() {
		unset($_SERVER['PHP_AUTH_USER']);
		unset($_SERVER['PHP_AUTH_PW']);
		$url = _getLoginUrl('https://my.repo:88/home');
		$this->assertEqual('https://my.repo:88/home', $url);
	}
	
	// ----------- below are integration tests ---------------
	
	/*
	 * should not work because login refuses repositories that do not match the local configuration
	function testVerifyLoginDemoAccount() {
		if (login_isSSLSupported()) {
			// test demo account authentication
			$_SERVER['PHP_AUTH_USER'] = 'svensson';
			$_SERVER['PHP_AUTH_PW'] = 'medel';
			$url = 'https://www.repos.se/sweden/svensson/trunk/';
			verifyLogin($url);
			$this->assertEqual(true, verifyLogin($url));
		}
	} */
	
	function test___integrationtests_below___() {
		$this->assertTrue(strBegins(TESTHOST, 'http'), "The integration tests below require a hostname TESTHOST");
		$this->assertTrue(strBegins(TESTREPO, TESTHOST) && strlen(TESTREPO)>strlen(TESTHOST), "The integration tests below require a TESTREPO");
		$this->sendMessage("The integration tests will run with the repository: ".TESTREPO);
		
		$url = parse_url(TESTREPO);
		$fp = fsockopen($url['host'], $url['scheme']=='https' ? 443 : 80, $errno, $errstr, 3);
		if (!$fp) {
   		$this->fail("Connection to test host timed out (3 s). Can not run integration tests.");
		} else {
			fclose($fp);
		}
	}

	function testVerifyLoginTestServer() {
		// test demo account authentication
		$_SERVER['PHP_AUTH_USER'] = 'test';
		$_SERVER['PHP_AUTH_PW'] = 'test';
		$url = TESTREPO.'/test/trunk/';
		verifyLogin($url);
		$this->assertEqual(true, verifyLogin($url));
	}
	
	function testVerifyLoginTestServerPercent() {
		$url = TESTREPO.'/demoproject/trunk/public/a%b';
		$this->assertTrue(verifyLogin($url), "Should find parent of $url and accept login");
		$this->assertNoErrors();
	}
	
	function testVerifyLoginTestServerUmlaut() {
		$url = TESTREPO.'/demoproject/trunk/public/aöb';
		$this->assertTrue(verifyLogin($url), "Should find parent of $url and accept login");
		$this->assertNoErrors();
	}
	
	function testVerifyLoginFail() {
		// test demo account authentication to repository root (no access there)
		$_SERVER['PHP_AUTH_USER'] = 'test';
		$_SERVER['PHP_AUTH_PW'] = 'test';
		$url = TESTREPO.'/demoproject/trunk/noaccess/';
		$this->assertEqual(false, verifyLogin($url));
	}
	
	function testGetHttpHeaders() {
		$headers = getHttpHeaders("http://www.repos.se/repos/");
		$this->assertTrue(count($headers) > 0);
		$this->assertEqual("HTTP/1.1 200 OK", $headers[0]);
	}
	
	function testGetHttpHeadersAuth() {	
		$headers = getHttpHeaders(TESTREPO.'/demoproject/trunk/noaccess/');
		$this->assertTrue(count($headers) > 0);
		$this->assertEqual("HTTP/1.1 403 Forbidden", $headers[0]);
	}
	
	// one of the test cases below may fail on for example SuSE 9.1 + Apache 2 + svn 1.2.3 where incorrect header is sent
	// see the selenium test case for invalid login
	
	function testGetHttpHeadersAuthenticationFailed() {
		$_SERVER['PHP_AUTH_USER'] = 'test';
		$_SERVER['PHP_AUTH_PW'] = 'qwery';
		$headers = getHttpHeaders(TESTREPO.'/test/trunk/');
		$this->assertTrue(count($headers) > 0);
		// with an invalid username, we expect another login attempt
		$this->assertEqual("HTTP/1.1 401 Authorization Required", $headers[0]);
	}

	function testGetHttpHeadersAuthorizationFailed() {
		$_SERVER['PHP_AUTH_USER'] = 'test';
		$_SERVER['PHP_AUTH_PW'] = 'test';
		// user "test" does not have access to repository root
		$headers = getHttpHeaders(TESTREPO.'/demoproject/trunk/noaccess/');
		$this->assertTrue(count($headers) > 0);
		// with a valid username, but no access according to ACL, we expect to see an access denied page
		$this->sendMessage("Subversion 1.2.x seems to return wrong code for folder that authenticated user can't access.");
		$this->assertEqual("HTTP/1.1 403 Forbidden", $headers[0]);
	}
	
	function testGetFirstNon404Parent() {
		$url = login_getFirstNon404Parent(TESTHOST."/repos/adsfawerwreq/does/not/exist.no", $status);
		$this->assertEqual(TESTHOST."/repos/", $url);
		$this->assertEqual(200, $status);
	}
		
	// ---------- HTTP header output for different login conditions ---------
	// not real tests
	
	function testGetHttpHeadersStart() {
		$headers = getHttpHeaders("http://www.repos.se/");
		echo("---- headers from the repos.se start page ----\n");
		$this->sendMessage($headers);
	}
	
	function testGetHttpHeadersInsecure() {
		$headers = getHttpHeaders(TESTREPO);
		echo("---- no login attempted, should be 401 Authorization Required with realm ----\n");
		$this->sendMessage($headers);
	}

	function testGetHttpHeadersInsecureAuth() {
		$headers = getHttpHeaders(TESTREPO,'nonexistinguser','qwerty');
		echo("---- login attempted but invalid credentials, should be 401 ----\n");
		$this->sendMessage($headers);
	}

	function testGetHttpHeadersInsecureAccessControl() {
		$headers = getHttpHeaders(TESTREPO,'test','test');
		echo("---- login ok, but access denied by ACL, should be 403 ----");
		$this->sendMessage($headers);
	}	
	
	function testGetHttpHeadersSecureAuth() {
		if(login_isSSLSupported()) {
			$headers = getHttpHeaders("https://www.repos.se/sweden");
			echo("---- SSL headers, no login: ----\n");
			$this->sendMessage($headers);
		} else {
			echo("SSL is not supported on this server\n");
		}
	}
	
}

testrun(new Login_include_Test());
?>
