<?php
require('login.inc.php');
require("../lib/simpletest/setup.php");

define('TESTHOST', getSelfRoot());
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
		$url = getSelfRoot().'/';
		$realm = getAuthName($url);
		$this->assertEqual(false, $realm);
	}
	
	// composition of target url (absolute URI)
	function testTargetUrl() {
		$_REQUEST[REPO_KEY] = 'http://my.repo';
		$_REQUEST['target'] = '/my/dir/file.txt';
		// REPO_KEY not effective in 1.1 // $this->assertEqual('http://my.repo/my/dir/file.txt', getTargetUrl());
		$this->assertTrue(strEnds(getTargetUrl(), '/my/dir/file.txt'));
		unset($_REQUEST[REPO_KEY]);
		unset($_REQUEST['target']);
	}
	
	function testTargetUrlFromPath() {
		$_REQUEST[REPO_KEY] = 'http://my.repo';
		// REPO_KEY not effective in 1.1 // $this->assertEqual('http://my.repo/h.txt', getTargetUrl('/h.txt'));
		$this->assertTrue(strEnds(getTargetUrl('/h.txt'), '/h.txt'));
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
	
	function testGetFirstNon404Parent() {
		$url = login_getFirstNon404Parent(TESTHOST."/repos/adsfawerwreq/does/not/exist.no", $status);
		$this->assertEqual(TESTHOST."/repos/", $url);
		$this->assertEqual(200, $status);
	}
	
}

testrun(new Login_include_Test());
?>
