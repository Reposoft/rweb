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
	
	function testGetWebappUrl() {
		$this->assertEqual(strstr(getWebappUrl(),'http'), getWebappUrl(), 'Should start with protocol. %s');
		$this->assertTrue(strpos(getWebappUrl(),'://'), getWebappUrl(), 'Should contain ://. %s');
	}
	
	function testGetRepositoryFromServerConfig() {
		unset($_REQUEST['base']);
		$repoFromConfiguration = getRepository();
		$this->assertTrue(strlen($repoFromConfiguration)>0);
		$_SERVER['REPOS_REPO'] = 'http://where-we-work.com/repo';
		$this->assertEqual('http://where-we-work.com/repo', getRepository());
	}

	function testGetRepositoryConfiguredRelativeToServerRoot() {
		// this syntax allows repository to be configured with any hostname and port
		unset($_REQUEST['base']);
		// fake apache setting
		$_SERVER['REPOS_REPO'] = '/my-repository';
		// host should be appended transparently when not explicitly set
		$this->assertEqual(getHost().'/my-repository', getRepository());
	}

	function testGetRepositoryWithBase() {
		$_REQUEST['base'] = 'me3';
		$_SERVER['REPOS_REPO'] = 'http://where-we-work.com/parent';
		// TODO maybe we need some syntax to disable multi-repo even if 'base' param is present
		$this->assertEqual('http://where-we-work.com/parent/me3', getRepository());
		$_REQUEST['base'] = '';
		$this->assertEqual('http://where-we-work.com/parent', getRepository(), 'Empty base does not count. %s');
	}

	function testGetRepositoryNotAffectedByAnythingFromClient() {
		$_SERVER['REPOS_REPO'] = 'http://localhost/svn';
		$_REQUEST['repo'] = 'http://where-we-work.com/svn';
		$this->assertFalse(strContains(getRepository(), 'where-we-work.com'), 
			'Should never be possible to change host or parent path using request or cookies. %s');
	}
	
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
	
	function testAsLinkNotAbsolute() {
		$_SERVER['HTTPS'] = 'on';
		$this->assertEqual('a/b/', asLink('a/b/'), 'Nothing should be added to relative url. %s');
		unset($_SERVER['HTTPS']);
		$this->assertEqual("/%26%25%23/", asLink("/&%#/"), 'asLink for paths should only call urlSpecialChars. %s');
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

	function testGetServiceSpecial() {
		$_SERVER['REQUEST_URI'] = '/repos-component/';
		$this->assertEqual(getService(),'home/','Repos root page is "home/". %s');
	}
	
	function testGetServiceAdmin() {
		$_SERVER['REQUEST_URI'] = '/repos-admin/account/';
		$this->assertEqual(getService(),'repos-admin/account/','repos-admin services should be prefixed with repos-admin. %s');
		$_SERVER['REQUEST_URI'] = '/repos-backup/store/';
		$this->assertEqual(getService(),'repos-backup/store/','repos-backup services should be prefixed with repos-backup. %s');
	}
	
}

testrun(new TestReposProperties());

?>
