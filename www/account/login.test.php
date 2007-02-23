<?php
require('login.inc.php');
require("../lib/simpletest/setup.php");
 
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
	
	function testVerifyLoginNotRepositoryUrl() {
		$this->sendMessage('It would be a serous secority risk if authentication accepts non-repository urls');
		$this->expectError(new PatternExpectation('* not a repository *'));
		// reportErrorInTest does not force 'return' so there will be extra errors
		$this->expectError(new AnythingExpectation());
		$this->expectError(new AnythingExpectation());
		$this->expectError(new AnythingExpectation());
		$result = verifyLogin(getSelfRoot());
		
		$this->expectError(new PatternExpectation('* not a repository *'));
		// reportErrorInTest does not force 'return' so there will be extra errors
		$this->expectError(new AnythingExpectation());
		$this->expectError(new AnythingExpectation());
		$result = verifyLogin(getSelfRoot().'/repos/'); // code exists if there is no trailing slash
		
	}
	
}

testrun(new Login_include_Test());
?>
