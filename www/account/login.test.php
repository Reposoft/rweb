<?php
require("../lib/simpletest/setup.php");

require 'login.inc.php';
 
define('TESTREPO', repos_getSelfRoot()."/testrepo/");
//define('TESTREPO', "http://test.repos.se/testrepo/");
//define('TESTREPO', "http://alto.optime.se/testrepo/");
 
class Login_include_Test extends UnitTestCase {

	public function setUp() {
	}
	
	public function testGetAuthNameReturnsSomething() {
		$url = TESTREPO;
		$realm = getAuthName($url);
		$this->assertTrue(strlen($realm)>0);
	}

	public function testGetAuthNameReturnsRepositoryRootUrl() {
		$url = TESTREPO;
		$realm = getAuthName($url);
		$this->assertTrue(TESTREPO, $realm);
	}	
	
	// this belongs to a system configuration test
	public function testGetAuthNameNoAuthAtServerRoot() {
		$url = repos_getSelfRoot().'/';
		$realm = getAuthName($url);
		$this->assertEqual(false, $realm);
	}
	
	// composition of target url (absolute URI)
	public function testTargetUrl() {
		$_REQUEST[REPO_KEY] = 'http://my.repo';
		$_REQUEST['target'] = '/my/dir/file.txt';
		$this->assertEqual('http://my.repo/my/dir/file.txt', getTargetUrl());
		unset($_REQUEST[REPO_KEY]);
		unset($_REQUEST['target']);
	}
	
	public function testTargetUrldecode() {
		
		//"https%3A%2F%2Fwww.repos.se%2Fsweden%2Fsvensson%2Ftrunk%2F"
	}
	
	// status code
	public function testgetHttpStatusFromHeader() {
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
	
	// getTargetUrl should return false if target can not be automatically resolved
	public function testGetTargetUrlFalse() {
		$this->assertEqual(false, getTargetUrl());
		$_GET['repo'] = 'http://my.repo';
		$this->assertEqual(false, getTargetUrl());
	}
	
	// url manipulation for the logged in user
	public function testGetLoginUrl() {
		$_SERVER['PHP_AUTH_USER'] = 'mE';
		$_SERVER['PHP_AUTH_PW'] = 'm&p8ss';
		$url = _getLoginUrl('https://my.repo:88/home');
		$this->assertEqual('https://mE:m&p8ss@my.repo:88/home', $url);
	}
	
	public function testGetLoginUrlNoUser() {
		unset($_SERVER['PHP_AUTH_USER']);
		unset($_SERVER['PHP_AUTH_PW']);
		$url = _getLoginUrl('https://my.repo:88/home');
		$this->assertEqual('https://my.repo:88/home', $url);
	}
	
	// belongs to a configuration test
	public function testVerifyLoginDemoAccount() {
		if (login_isSSLSupported()) {
			// test demo account authentication
			$_SERVER['PHP_AUTH_USER'] = 'svensson';
			$_SERVER['PHP_AUTH_PW'] = 'medel';
			$url = 'https://www.repos.se/sweden/svensson/trunk';
			verifyLogin($url);
			$this->assertEqual(true, verifyLogin($url));
		}
	}

	public function testVerifyLoginTestServer() {
		// test demo account authentication
		$_SERVER['PHP_AUTH_USER'] = 'test';
		$_SERVER['PHP_AUTH_PW'] = 'test';
		$url = TESTREPO.'test/trunk/';
		verifyLogin($url);
		$this->assertEqual(true, verifyLogin($url));
	}
	
	public function testVerifyLoginFail() {
		// test demo account authentication to repository root (no access there)
		$_SERVER['PHP_AUTH_USER'] = 'test';
		$_SERVER['PHP_AUTH_PW'] = 'test';
		$url = TESTREPO;
		$this->assertEqual(false, verifyLogin($url));
	}
	
	public function testGetHttpHeaders() {
		$headers = getHttpHeaders("http://www.google.se/");
		$this->assertTrue(count($headers) > 0);
		$this->assertEqual("HTTP/1.0 200 OK", $headers[0]);
	}
	
	public function testGetHttpHeadersAuth() {	
		$headers = getHttpHeaders(TESTREPO);
		$this->assertTrue(count($headers) > 0);
		$this->assertEqual("HTTP/1.1 401 Authorization Required", $headers[0]);
	}
	
	// one of the test cases below may fail on for example SuSE 9.1 + Apache 2 + svn 1.2.3 where incorrect header is sent
	// see the selenium test case for invalid login
	
	public function testGetHttpHeadersAuthenticationFailed() {
		$headers = my_get_headers(TESTREPO,'nonexistinguser','qwerty');
		$this->assertTrue(count($headers) > 0);
		// with an invalid username, we expect another login attempt
		$this->assertEqual("HTTP/1.1 401 Authorization Required", $headers[0]);
	}

	public function testGetHttpHeadersAuthorizationFailed() {
		// user "test" does not have access to repository root
		$headers = my_get_headers(TESTREPO,'test','test');
		$this->assertTrue(count($headers) > 0);
		// with a valid username, but no access according to ACL, we expect to see an access denied page
		$this->assertEqual("HTTP/1.1 403 Forbidden", $headers[0]);
	}
	
	function testGetFirstNon404Parent() {
		$url = login_getFirstNon404Parent("http://localhost/repos/adsfawerwreq/does/not/exist.no", $status);
		$this->assertEqual("http://localhost/repos/", $url);
		$this->assertEqual(200, $status);
	}
		
	// ---------- HTTP header output for different login conditions ---------
	// not real tests
	
	public function testGetHttpHeadersStart() {
		$headers = getHttpHeaders("http://www.repos.se/");
		echo("<pre>---- headers from the repos.se start page ----\n");
		print_r($headers);
		echo("</pre>\n");
	}
	
	public function testGetHttpHeadersInsecure() {
		$headers = getHttpHeaders(TESTREPO);
		echo("<pre>---- no login attempted, should be 401 Authorization Required with realm ----\n");
		print_r($headers);
		echo("</pre>\n");
	}

	public function testGetHttpHeadersInsecureAuth() {
		$headers = getHttpHeaders(TESTREPO,'nonexistinguser','qwerty');
		echo("<pre>---- login attempted but invalid credentials, should be 401 ----\n");
		print_r($headers);
		echo("</pre>\n");
	}

	public function testGetHttpHeadersInsecureAccessControl() {
		$headers = getHttpHeaders(TESTREPO,'test','test');
		echo("<pre>---- login ok, but access denied by ACL, should be 403 ----\n");
		print_r($headers);
		echo("</pre>\n");
	}	
	
	public function testGetHttpHeadersSecureAuth() {
		if(login_isSSLSupported()) {
			$headers = getHttpHeaders("https://www.repos.se/sweden");
			echo("<pre>---- SSL headers, no login: ----\n");
			print_r($headers);
			echo("</pre>\n");
		} else {
			echo("<pre>SSL is not supported on this server</pre>\n");
		}
	}
	
}

testrun(new Login_include_Test());
?>
