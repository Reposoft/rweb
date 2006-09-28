<?php
require_once(dirname(__FILE__).'/repos.properties.php');
require("../lib/simpletest/setup.php");

class TestReposProperties extends UnitTestCase {

	function TestReposProperties() {
		$this->UnitTestCase();
	}

	function testBeginsWith() {
		$this->assertTrue(beginsWith('/a', '/'));
		$this->assertFalse(beginsWith('a/', '/'));
		$this->assertFalse(beginsWith('', '/'));
		$this->assertFalse(beginsWith(null, '/'));
		$this->assertFalse(beginsWith(3, '/'));
	}

	function testEndsWith() {
		$this->assertTrue(endsWith('a/', '/'));
		$this->assertFalse(endsWith('/a', '/'));
		$this->assertFalse(endsWith('', '/'));
		$this->assertFalse(endsWith(null, '/'));
		$this->assertFalse(endsWith(3, '/'));
	}	
	
	function testGetTempDir() {
		$dir = getTempDir();
		$this->assertTrue(endsWith($dir, DIRECTORY_SEPARATOR));
		$this->assertTrue(file_exists($dir));
		$this->assertTrue(is_writable($dir));
	}

	function testGetTempnamDir() {
		$dir1 = getTempnamDir();
		$dir2 = getTempnamDir();
		$this->assertTrue(file_exists($dir1));
		$this->assertTrue(is_writable($dir1));
		$this->assertNotEqual($dir1, $dir2);
	}
	
	function testGetTempnamDirName() {
		$dir1 = getTempnamDir('mytest');
		$this->assertTrue(endsWith($dir1, DIRECTORY_SEPARATOR));
		$this->assertTrue(strpos($dir1, DIRECTORY_SEPARATOR.'mytest'.DIRECTORY_SEPARATOR)>0);
	}
	
	function testRemoveTempDir() {
		$dir = getTempnamDir();
		mkdir($dir.'new folder/');
		mkdir($dir.'.svn/');
		touch($dir.'.svn/test.txt');
		removeTempDir($dir);
		$this->assertFalse(file_exists($dir.'new folder/'));
		$this->assertFalse(file_exists($dir.'.svn/test.txt'));
		$this->assertFalse(file_exists($dir.'.svn/'));
	}
	
	function testRemoveTempDirWriteProtected() {
		$dir = getTempnamDir();
		// the svn client makes the .svn folder write protected in windows
		mkdir($dir.'.svn/', 0400);
		touch($dir.'.svn/test.txt');
		$this->assertTrue(chmod($dir.'.svn/test.txt', 0400));
		removeTempDir($dir);
		$this->assertFalse(file_exists($dir.'.svn/test.txt'));
		$this->assertFalse(file_exists($dir.'.svn/'));
	}	
	
	function testRemoveTempDirInvalid() {
		$dir = "/this/is/any/kind/of/dir";
		removeTempDir($dir);
		$this->assertError('Will not remove non-temp dir /this/is/any/kind/of/dir.');
	}
	
	// test for the portability functions
	
	public function testIsWindows() {
		if (DIRECTORY_SEPARATOR=='\\') {
			$this->assertTrue(isWindows());
		} else {
			$this->assertFalse(isWindows());
		}
	}
	
	// the tests below modify server variables, so they might affect other tests. Should maybe use simpletest mock server instead.
	
	public function testGetSelfUrl() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '';
		$this->assertEqual('http://my.host', repos_getSelfUrl());
	}

	public function testGetSelfUrlS() {
		$_SERVER['SERVER_PORT'] = 443;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEqual('https://my.host/', repos_getSelfUrl());
	}
	
	public function testGetSelfUrlPort() {
		$_SERVER['SERVER_PORT'] = 123;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEqual('http://my.host:123/', repos_getSelfUrl());
	}

	public function testGetSelfUrlPortS() {
		$_SERVER['SERVER_PORT'] = 123;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEqual('https://my.host:123/', repos_getSelfUrl());
	}

	public function testGetSelfUrlFile() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/index.html';
		$this->assertEqual('http://my.host/index.html', repos_getSelfUrl());
	}
	
	public function testGetSelfUrlPath() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/home/';
		$this->assertEqual('http://my.host/home/', repos_getSelfUrl());
	}
	
	public function testGetSelfUrlQ() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/test/?';
		$this->assertEqual('http://my.host/test/', repos_getSelfUrl());
	}

	public function testGetSelfUrlQuery() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/index.html?variable';
		$this->assertEqual('http://my.host/index.html', repos_getSelfUrl());
	}

	public function testGetSelfUrlQuery2() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/test/?variable=value&another';
		$this->assertEqual('http://my.host/test/', repos_getSelfUrl());
	}
	
}

$test = &new TestReposProperties();
$test->run(new HtmlReporter());

?>
