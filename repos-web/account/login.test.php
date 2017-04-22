<?php
require('login.inc.php');
require("../lib/simpletest/setup.php");
 
class Login_include_Test extends UnitTestCase {
	
	function setUp() {
		// these tests should not rely on a logged in browser
	}
	
	function testGetReposUserEncode() {
		setTestUser("A B");
		$this->assertEqual("A B", getReposUser(), "Username should not be encoded");
		setTestUser("A+B");
		$this->assertEqual("A+B", getReposUser());
		setTestUserNotLoggedIn();
	}
	
	// this belongs to a system configuration test
	function testGetAuthNameNoAuthAtServerRoot() {
		$url = getSelfRoot().'/';
		$realm = getAuthName($url);
		$this->assertEqual(false, $realm);
	}
	
	// composition of target url (absolute URI)
	function testTargetUrl() {
		$_SERVER['REPOS_REPO'] = 'http://my.repo';
		$_REQUEST['target'] = '/my/dir/file.txt';
		// REPO_KEY not effective in 1.1 // $this->assertEqual('http://my.repo/my/dir/file.txt', getTargetUrl());
		$this->assertTrue(strEnds(getTargetUrl(), '/my/dir/file.txt'));
		unset($_SERVER['REPOS_REPO']);
		unset($_REQUEST['target']);
	}
	
	function testTargetUrlFromPath() {
		$_SERVER['REPOS_REPO'] = 'http://my.repo';
		// REPO_KEY not effective in 1.1 // $this->assertEqual('http://my.repo/h.txt', getTargetUrl('/h.txt'));
		$this->assertTrue(strEnds(getTargetUrl('/h.txt'), '/h.txt'));
		unset($_SERVER['REPOS_REPO']);
	}
	
	// getTargetUrl should throw error if there is no target
	function testGetTargetUrlFalse() {
		$this->expectError();
		$this->expectError();
		getTargetUrl();
	}
	
	// url manipulation for the logged in user
	function testGetLoginUrl() {
		setTestUser('mE', 'm&p8ss');
		$url = _getLoginUrl('https://my.repo:88/home');
		$this->assertEqual('https://mE:m&p8ss@my.repo:88/home', $url);
	}
	
	function testGetLoginUrlNoUser() {
		setTestUserNotLoggedIn();
		$url = _getLoginUrl('https://my.repo:88/home');
		$this->assertEqual('https://my.repo:88/home', $url);
	}
	
	function testVerifyLoginNotRepositoryUrl() {
		$this->dump(null, 'It would be a serous secority risk if authentication accepts non-repository urls');
		$this->expectError(new PatternExpectation('* not a repository *'));
		// reportErrorInTest does not force 'return' so there will be extra errors
		$this->expectError(new AnythingExpectation());
		$this->expectError(new AnythingExpectation()); // one too many of these may cause error in next test
		$result = verifyLogin(getSelfRoot());
	}
	
	function testVerifyLoginNotRepositoryUrl2() {
		$this->expectError(new PatternExpectation('* not a repository *'));
		// reportErrorInTest does not force 'return' so there will be extra errors
		$this->expectError(new AnythingExpectation());
		$this->expectError(new AnythingExpectation());
		$result = verifyLogin(getSelfRoot().'/repos/'); // code exits if there is no trailing slash
	}
	
	function testGetTarget() {
		$_REQUEST['target'] = '/demoproject/trunk/+/';
		$this->assertEqual('/demoproject/trunk/+/', getTarget());
		$_REQUEST['target'] = '/demoproject/trunk/&/';
		$this->assertEqual('/demoproject/trunk/&/', getTarget());
		// PHP does one transparent urldecode before the value goes superglobal.
		// (test echo($_REQUEST['target']) in login_getQueryParam)
		// so this target is actually %2525 in browser address
		$_REQUEST['target'] = '/demoproject/trunk/%25/';
		// so we should not do another decode
		$this->assertEqual('/demoproject/trunk/%25/', getTarget());
	}
	
	function testGetTargetUrlMetacharacters() {
		// log xml does not contain escaped hrefs, so it relies on browser's encoding
		// browser does not encode + and &
		$_REQUEST['target'] = '/demoproject/a+b/';
		$this->assertEqual('/demoproject/a+b/', getTarget());
		$_REQUEST['target'] = '/demoproject/a&b/';
		$this->assertEqual('/demoproject/a&b/', getTarget());
		// "=" is no problem in parameter values, because it can't be a metacharacter until after next "&"
		$_REQUEST['target'] = '/demoproject/a=b/';
		$this->assertEqual('/demoproject/a=b/', getTarget());
	}
	
	function testAsLogoutUrl() {
		$url = '/repos/admin/';
		$this->assertEqual('/?logout&go='.rawurlencode($url), asLogoutUrl($url));
		$url = '/?login';
		$this->assertEqual('/?logout&go='.rawurlencode($url), asLogoutUrl($url));
		$url = 'http://localhost/repos/';
		$this->assertEqual('/?logout&go='.rawurlencode($url), asLogoutUrl($url));
	}
	
}

$testcase = new Login_include_Test();
testrun($testcase);
?>
