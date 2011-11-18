<?php
require('login.inc.php');
require("../lib/simpletest/setup.php");

// assume that if repository /testrepo exists we have a proper intergration test repository on this host
define('TESTHOST', getHost());
define('TESTREPO', getRepository());
 
class Login_integraton_Test extends UnitTestCase {
	
	function setUp() {
	}

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
		$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 3);
		if (!$fp) {
   		$this->fail("Connection to test host timed out (3 s). Can not run integration tests.");
		} else {
			fclose($fp);
		}
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

	// test demo account authentication
	function testVerifyLoginTestServer() {
		setTestUser();
		$url = TESTREPO.'/test/trunk/';
		$result = verifyLogin($url);
		$this->assertEqual(true, $result);
	}
	
	function testVerifyLoginSpaces() {
		setTestUser('Test User');
		$url = TESTREPO.'/Test User/'; // should not be encoded
		$result = verifyLogin($url);
		$this->assertEqual(true, $result);
	}	
	
	function testVerifyLoginTestServerSpecialchars() {
		$url = TESTREPO.'/t-e_st@user.acc/';
		//$this->assertTrue(verifyLogin($url), "Should find $url account and accept login");
		// not sure this is supported in apache
		$this->sendMessage("Login to $url: ".(verifyLogin($url) ? 'yes' : 'no'));
		$this->assertNoErrors();
	}
	
	function testVerifyLoginTestServerUmlaut() {
		$this->sendMessage('This test requires the PHP file to be UTF-8 encoded');
		$url = TESTREPO.'/téstüsär/';
		//$this->assertTrue(verifyLogin($url), "Should find $url account and accept login");
		// not sure this is supported in apache
		$this->sendMessage("Login to $url: ".(verifyLogin($url) ? 'yes' : 'no'));
		$this->assertNoErrors();
	}
	
	// test demo account authentication to repository root (no access there)
	function testVerifyLoginFail() {
		setTestUser();
		$url = TESTREPO.'/demoproject/trunk/noaccess/';
		$this->assertEqual(false, verifyLogin($url));
	}
	
	function testGetFirstNon404Parent() {
		$url = login_getFirstNon404Parent(getWebappUrl()."adsfawerwreq/does/not/exist.no", $status);
		$this->assertEqual(getWebappUrl(), $url);
		// this test url should be a normal resource, not a redirect
		$this->assertEqual(200, $status);
	}
	
	function testVerifyLoginNonexistingBelow403() {
		$this->sendMessage('It is essential that the server sends 403, not 404, for a missing folder inside Forbidden');
		setTestUser();
		$url = TESTREPO.'/svensson/nonexistingxyz/';
		$result = verifyLogin($url);
		$this->assertEqual(false, $result);
	}	

	function testVerifyLoginNonexisting() {
		$this->sendMessage('If we get 404 inside the repository we know that login was successful');
		setTestUser();
		$url = TESTREPO.'/test/nonexistingxyz/';
		$result = verifyLogin($url);
		$this->assertEqual(true, $result);
	}
	
}

testrun(new Login_integraton_Test());
?>
