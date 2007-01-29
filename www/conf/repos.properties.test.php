<?php
require_once(dirname(__FILE__).'/repos.properties.php');
require("../lib/simpletest/setup.php");

class TestReposProperties extends UnitTestCase {

	function TestReposProperties() {
		$this->UnitTestCase();
	}
	
	// -------- configuration -------- 
	
	function testGetWebapp() {
		$this->assertTrue(strEnds(getWebapp(),'/repos/'), "Currently repos must be installed in /repos/");
	}
	
	/* disabled multi-repository functionality
	function testGetRepository() {
		global $_repos_config;
		unset($_REQUEST[REPO_KEY]);
		unset($_COOKIE[REPO_KEY]);
		$this->assertTrue(strlen(getRepository())>0);
		$this->assertTrue(strContains(getRepository(), '://'), "getRepository() should return a full url");
		$real = $_repos_config['repositories'];
		$_repos_config['repositories'] = "http://my.host/repo/";
		$this->assertEqual('http://my.host/repo/', getRepository());
		// test multiple configured repositories
		$_repos_config['repositories'] = "http://my.host/repo1/, http://my.host/repo2/";
		$this->assertEqual('http://my.host/repo1/', getRepository());
		// if there is a cookie referer should have no effect
		$_SERVER['HTTP_REFERER'] = 'http://my.host/repo2/file.txt';
		$this->assertEqual('http://my.host/repo2/', getRepository());
		unset($_SERVER['HTTP_REFERER']);
		$_repos_config['repositories'] = $real;
	}
	
	function testGetRepositoryFromRepoParameterAndCookie() {
		$_COOKIE[REPO_KEY] = "https://host/c/";
		$_REQUEST['repo'] = "https://host/r/";
		$this->assertEqual('https://host/r/', getRepository());
		unset($_REQUEST['repo']);
		$this->assertEqual('https://host/c/', getRepository());
	}
	*/
	
	function testUrlEncodeNames() {
		$this->assertEqual('https://host/r%25p', urlEncodeNames('https://host/r%p'));
		$this->assertEqual('http://host:80/r%22/?s%25t', urlEncodeNames('http://host:80/r"/?s%t'));
		$this->assertEqual("/r%3As/t%20/?u%3F", urlEncodeNames("/r:s/t /?u?"));
	}
	
	function testGetSelfUrl() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '';
		$this->assertEqual('http://my.host', getSelfUrl());
	}

	function testGetSelfUrlS() {
		$_SERVER['SERVER_PORT'] = 443;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEqual('https://my.host/', getSelfUrl());
	}
	
	function testGetSelfUrlPort() {
		$_SERVER['SERVER_PORT'] = 123;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEqual('http://my.host:123/', getSelfUrl());
	}

	function testGetSelfUrlPortS() {
		$_SERVER['SERVER_PORT'] = 123;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEqual('https://my.host:123/', getSelfUrl());
	}

	function testGetSelfUrlFile() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/index.html';
		$this->assertEqual('http://my.host/index.html', getSelfUrl());
	}
	
	function testGetSelfUrlPath() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/home/';
		$this->assertEqual('http://my.host/home/', getSelfUrl());
	}
	
	function testGetSelfUrlQ() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/test/?';
		$this->assertEqual('http://my.host/test/', getSelfUrl());
	}

	function testGetSelfUrlQuery() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/index.html?variable';
		$this->assertEqual('http://my.host/index.html', getSelfUrl());
	}

	function testGetSelfUrlQuery2() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/test/?variable=value&another';
		$this->assertEqual('http://my.host/test/', getSelfUrl());
	}
	
}

testrun(new TestReposProperties());

?>
