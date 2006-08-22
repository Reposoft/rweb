<?php
require_once 'PHPUnit/Framework/TestCase.php';

require '../../account/login.inc.php';
 
class LoginTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
		unset($_GET);
		unset($_SERVER);
		global $_GET, $_SERVER;
	}

	public function testGetHttpHeadersInsecure() {
		$headers = getHttpHeaders("http://svn.optime.se/optime");
		echo("---- test output: ----\n");
		print_r($headers);
	}

	public function testGetHttpHeadersInsecureAuth() {
		$headers = getHttpHeaders("http://svn.optime.se/optime",'nonexistinguser','qwerty');
		echo("---- test output: ----\n");
		print_r($headers);
	}
	
	public function testGetAuthName() {
		$url = "http://svn.optime.se/optime";
		$realm = getAuthName($url);
		$this->assertEquals("Optime", $realm);
	}
	
	public function testGetAuthNameNoAuth() {
		$url = "http://www.google.se";
		$realm = getAuthName($url);
		$this->assertEquals(false, $realm);
	}
	
	// target is a file in the repository, absolute url from repository root
	public function testTargetTarget() {
		$_GET['target'] = '/my/file.txt';
		$target = getTarget();
		$this->assertEquals('/my/file.txt', $target);
	}
	
	// target is a directory, absolute url from repository root, no tailing slash
	// should always return dir _with_ tailing slash
	public function testTargetPath() {
		$_GET['path'] = '/my/dir';
		$target = getTarget();
		$this->assertEquals('/my/dir/', $target);
	}
	
	// target is both path and file
	public function testTargetPathFile() {
		$_GET['path'] = '/my/dir';
		$_GET['file'] = 'file.txt';
		$target = getTarget();
		$this->assertEquals('/my/dir/file.txt', $target);
	}
	
	// target is both path and file
	public function testTargetPathShashFile() {
		$_GET['path'] = '/my/dir/';
		$_GET['file'] = 'file.txt';
		$target = getTarget();
		$this->assertEquals('/my/dir/file.txt', $target);
	}
	
	// repository url when repo param is set
	public function testRepositoryUrlRepo() {
		$_GET['repo'] = 'http://my.repo/';
		$this->assertEquals('http://my.repo', getRepositoryUrl());
	}
	
	// repository url from referrer and path
	public function testRepositoryUrlReferrerPath() {
		$_SERVER['HTTP_REFERER'] = 'http://my.repo/my/dir';
		$_GET['path'] = '/my/dir';
		$this->assertEquals('http://my.repo', getRepositoryUrl());
	}
	
	public function testRepositoryUrlReferrerPathSlash() {
		$_SERVER['HTTP_REFERER'] = 'http://my.repo/my/dir';
		$_GET['path'] = '/my/dir/';
		// according to function docs this should return nothing
		$this->assertEquals('http://my.repo', getRepositoryUrl());
	}
	
	// composition of target url (absolute URI)
	public function testTargetUrl() {
		$_GET['repo'] = 'http://my.repo';
		$_GET['target'] = '/my/dir/file.txt';
		// according to function docs this should return nothing
		$this->assertEquals('http://my.repo/my/dir/file.txt', getTargetUrl());
	}
	
	public function testTargetUrldecode() {
		
		//"https%3A%2F%2Fwww.repos.se%2Fsweden%2Fsvensson%2Ftrunk%2F"
	}
	
	// getTargetUrl should return false if target can not be automatically resolved
	public function testGetTargetUrlFalse() {
		$this->assertEquals(false, getTargetUrl());
		$_GET['repo'] = 'http://my.repo';
		$this->assertEquals(false, getTargetUrl());
	}
	
	// url manipulation for the logged in user
	public function testGetLoginUrl() {
		$_SERVER['PHP_AUTH_USER'] = 'mE';
		$_SERVER['PHP_AUTH_PW'] = 'm&p8ss';
		$url = getLoginUrl('https://my.repo:88/home');
		$this->assertEquals('https://mE:m&p8ss@my.repo:88/home', $url);
	}
	
	public function testGetLoginUrlNoUser() {
		unset($_SERVER['PHP_AUTH_USER']);
		unset($_SERVER['PHP_AUTH_PW']);
		$url = getLoginUrl('https://my.repo:88/home');
		$this->assertEquals('https://my.repo:88/home', $url);
	}
	
	public function testVerifyLogin() {
		if (isSSLSupported()) {
			// test demo account authentication
			$_SERVER['PHP_AUTH_USER'] = 'svensson';
			$_SERVER['PHP_AUTH_PW'] = 'medel';
			$url = 'https://www.repos.se/sweden/svensson/trunk';
			verifyLogin($url);
			$this->assertEquals(true, verifyLogin($url));
		}
	}
	
	public function testVerifyLoginFail() {
		if (isSSLSupported()) {
			// test demo account authentication
			$_SERVER['PHP_AUTH_USER'] = 'svensson';
			$_SERVER['PHP_AUTH_PW'] = 'medel';
			$url = 'https://www.repos.se/sweden';
			$this->assertEquals(false, verifyLogin($url));
		}
	}
	
	public function testGetHttpHeaders() {
		$headers = getHttpHeaders("http://www.google.se/");
		$this->assertTrue(count($headers) > 0);
		$this->assertEquals("HTTP/1.0 200 OK", $headers[0]);
	}
	
	public function testGetHttpHeadersAuth() {
	if (isSSLSupported()) {	
		$headers = getHttpHeaders("https://www.repos.se/sweden");
		$this->assertTrue(count($headers) > 0);
		$this->assertEquals("HTTP/1.1 401 Authorization Required", $headers[0]);
	}
	}
	
	public function testGetHttpHeadersAuthFailed() {
	if (isSSLSupported()) {
		$headers = my_get_headers("https://www.repos.se/sweden",'nonexistinguser','qwerty');
		$this->assertTrue(count($headers) > 0);
		$this->assertEquals("HTTP/1.1 401 Unauthorized", $headers[0]);
	}
	}

}
?>