<?php
require("../lib/simpletest/setup.php");

require 'login.inc.php';
 
//define('TESTREPO', "http://test.repos.se/testrepo");
define('TESTREPO', "http://alto.optime.se/testrepo");
 
class Login_include_Test extends UnitTestCase {

	public function setUp() {
		// some server varables are used in the test below and must be reset every time
		unset($_GET['target']);
		unset($_GET['path']);
		unset($_GET['file']);
		unset($_GET['repo']);
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
	
	public function testGetAuthName() {
		$url = TESTREPO;
		$realm = getAuthName($url);
		$this->assertEqual("Optime", $realm);
	}
	
	public function testGetAuthNameNoAuth() {
		$url = "http://www.google.se";
		$realm = getAuthName($url);
		$this->assertEqual(false, $realm);
	}
	
	// target is a file in the repository, absolute url from repository root
	public function testTargetTarget() {
		$_GET['target'] = '/my/file.txt';
		$target = getTarget();
		$this->assertEqual('/my/file.txt', $target);
	}
	
	// target is a directory, absolute url from repository root, no tailing slash
	// should always return dir _with_ tailing slash
	public function testTargetPath() {
		$_GET['path'] = '/my/dir';
		$target = getTarget();
		$this->assertEqual('/my/dir/', $target);
	}
	
	// target is both path and file
	public function testTargetPathFile() {
		$_GET['path'] = '/my/dir';
		$_GET['file'] = 'file.txt';
		$target = getTarget();
		$this->assertEqual('/my/dir/file.txt', $target);
	}
	
	// target is both path and file
	public function testTargetPathShashFile() {
		$_GET['path'] = '/my/dir/';
		$_GET['file'] = 'file.txt';
		$target = getTarget();
		$this->assertEqual('/my/dir/file.txt', $target);
	}
	
	// repository url when repo param is set
	public function testRepositoryUrlRepo() {
		$_GET['repo'] = 'http://my.repo/';
		$this->assertEqual('http://my.repo', getRepositoryUrl());
	}
	
	// repository url from referrer and path
	public function testRepositoryUrlReferrerPath() {
		$_SERVER['HTTP_REFERER'] = 'http://my.repo/my/dir';
		$_GET['path'] = '/my/dir';
		$this->assertEqual('http://my.repo', getRepositoryUrl());
	}
	
	public function testRepositoryUrlReferrerPathSlash() {
		$_SERVER['HTTP_REFERER'] = 'http://my.repo/my/dir';
		$_GET['path'] = '/my/dir/';
		// according to function docs this should return nothing
		$this->assertEqual('http://my.repo', getRepositoryUrl());
	}
	
	// composition of target url (absolute URI)
	public function testTargetUrl() {
		$_GET['repo'] = 'http://my.repo';
		$_GET['target'] = '/my/dir/file.txt';
		// according to function docs this should return nothing
		$this->assertEqual('http://my.repo/my/dir/file.txt', getTargetUrl());
	}
	
	public function testTargetUrldecode() {
		
		//"https%3A%2F%2Fwww.repos.se%2Fsweden%2Fsvensson%2Ftrunk%2F"
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
		$url = getLoginUrl('https://my.repo:88/home');
		$this->assertEqual('https://mE:m&p8ss@my.repo:88/home', $url);
	}
	
	public function testGetLoginUrlNoUser() {
		unset($_SERVER['PHP_AUTH_USER']);
		unset($_SERVER['PHP_AUTH_PW']);
		$url = getLoginUrl('https://my.repo:88/home');
		$this->assertEqual('https://my.repo:88/home', $url);
	}
	
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
		$url = TESTREPO.'/test/trunk';
		verifyLogin($url);
		$this->assertEqual(true, verifyLogin($url));
	}
	
	public function testVerifyLoginFail() {
		// test demo account authentication
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
	
}

$test = &new Login_include_Test();
$test->run(new HtmlReporter());
?>