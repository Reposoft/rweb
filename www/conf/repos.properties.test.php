<?php
require_once(dirname(__FILE__).'/repos.properties.php');
require("../lib/simpletest/setup.php");

class TestReposProperties extends UnitTestCase {

	function TestReposProperties() {
		$this->UnitTestCase();
	}
	
	// -------- configuration -------- 
	
	function testGetWebapp() {
		$this->assertTrue(strlen(getWebapp())>0, "There should be a webapp url. %s");
		$this->assertTrue(!strContains(getWebapp(),'https:'), "Webapp should normally be plain http. %s");
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
		$this->assertEqual('http://host:80/r%22/?a=s%25t', urlEncodeNames('http://host:80/r"/?a=s%t'));
		$this->assertEqual("/r%3As/t%20/?b=u%3F", urlEncodeNames("/r:s/t /?b=u?"));
	}
	
	function testUrlEncodeNamesQuery() {
		$this->assertEqual('http://host/?a=b%2Bb&c=d%25e', urlEncodeNames('http://host/?a=b+b&c=d%e'));
	}

	function testGetHost() {
		$_SERVER['SERVER_PORT'] = 443;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_NAME'] = 'my.host';
		$this->assertEqual('http://my.host', getHost(), 'getHost should always return http, not ssl. %s');
		$_SERVER['SERVER_PORT'] = 1443;
		$this->assertEqual('http://my.host', getHost(), 'Never use port from https. %s');
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
		//$this->assertEqual('https://my.host:123/', getSelfUrl());
		$this->assertEqual('https://my.host/', getSelfUrl(), 'Repos does not support custom port on https. %s');
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
	
	function testAsLink() {
		unset($_SERVER['HTTPS']);
		$host = 'http://my.host:1088/a/b/c/d';
		$this->assertEqual('http://my.host:1088/a/b/c/d',
			asLink($host), 'asLink should not change http url unless HTTPS=on. %s');
	}
	
	function testAsLinkHttps() {
		// test that asLink calls urlSpecialChars
		$_SERVER['HTTPS'] = 'on';
		$this->assertEqual('https://my.host', asLink('http://my.host'),
			'asLink should change to https when HTTPS=on. %s');
		$this->sendMessage('administrator can only configure the non-ssl url, and we cant guess SSL port number');
		$this->assertEqual('https://my.host', asLink('http://my.host:88'),
			'Currently repos does not support custom port number for SSL. Should be removed. %s');
		$this->assertEqual('https://my.host/a/b', asLink('http://my.host:88/a/b'),
			'Currently repos does not support custom port number for SSL. Should be removed. %s');
				
		$this->assertEqual('https://my.host', asLink('https://my.host'), 'Request is already HTTPS. Do nothing. %s');
		$this->assertEqual('https://my.host:1443', asLink('https://my.host:1443'), 'Keep https and port. %s');		
		unset($_SERVER['HTTPS']);
		$this->assertEqual('https://my.host', asLink('https://my.host'), 'Url is alreadh HTTPS. Do nothing. %s');
	}
	
	function testAsLinkSpecialChars() {
		unset($_SERVER['HTTPS']);
		$host = getSelfRoot();
		$this->assertEqual("$host/%26%25%23/",
			asLink("$host/&%#/"), 'asLink should call urlSpecialChars. %s');
	}
	
	function testUrlSpecialChars() {
		// percent must be encoded before any other encoding
		$this->assertEqual('http://my.host/%25',
			urlSpecialChars('http://my.host/%'));
		// avoid ampersands, they have different encoding in different contexts
		$this->assertEqual('http://my.host/%26',
			urlSpecialChars('http://my.host/&'));
		// browser can't know if bracket is for a section name, causes 404 page not found
		$this->assertEqual('http://my.host/%23',
			urlSpecialChars('http://my.host/#'));		
	}
	
	function testUrlSpecialCharsQueryString() {
		// luckliy question mark is not allowed in filenames
		$this->assertEqual('http://my.host/%26%25%23/?a&%#',
			urlSpecialChars('http://my.host/&%#/?a&%#'));
		
	}
	
	function testGetRepositoryDefault() {
		$r = getRepositoryDefault();
		$this->assertPattern('/[\w-]+$/', $r, 'Repository should not end with slash. %s');
	}
	
	function testIsRepositoryUrl() {
		$this->sendMessage('repository:', getRepository());
		$this->assertTrue(isRepositoryUrl(getRepository()),getRepository().' is a repository url. %s');
		$this->assertFalse(isRepositoryUrl(getSelfRoot()),getSelfRoot().' is not a repository url. %s');
		$this->assertFalse(isRepositoryUrl(getSelfRoot().'/repos/'),getSelfRoot().'/repos/ is not a repository url. %s');
	}
	
	function testGetService() {
		$_SERVER['REQUEST_URI'] = '/repos/open/';
		$this->assertEqual(getService(),'open/','repos-service should be the uri without webapp and without file. %s');
		$_SERVER['REQUEST_URI'] = '/repos/open/index.php';
		$this->assertEqual(getService(),'open/','Script filename is not part of the service, only folders. %s');
		$_SERVER['REQUEST_URI'] = '/repos/open/file/';
		$this->assertEqual(getService(),'open/file/','Subfolder. %s');
		$_SERVER['REQUEST_URI'] = '/subfolder/repos/open/';
		$this->assertEqual(getService(),'repos/open/','Currently we assume that repos is a top level folder. %s');
	}
	
}

testrun(new TestReposProperties());

?>
