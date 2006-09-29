<?php
require_once(dirname(__FILE__).'/repos.properties.php');
require("../lib/simpletest/setup.php");

class TestReposProperties extends UnitTestCase {

	function TestReposProperties() {
		$this->UnitTestCase();
	}
	
	// ------- string functions --------

	function testStrBegins() {
		$this->assertTrue(strBegins('/a', '/'));
		$this->assertFalse(strBegins('a/', '/'));
		$this->assertFalse(strBegins('', '/'));
		$this->assertFalse(strBegins(null, '/'));
		$this->assertFalse(strBegins(3, '/'));
	}

	function testStrEnds() {
		$this->assertTrue(strEnds('a/', '/'));
		$this->assertFalse(strEnds('/a', '/'));
		$this->assertFalse(strEnds('', '/'));
		$this->assertFalse(strEnds(null, '/'));
		$this->assertFalse(strEnds(3, '/'));
	}
	
	function testStrContains() {
		$this->assertTrue(strContains('xy', 'x'));
		$this->assertFalse(strContains('x', 'xy'));
		$this->assertTrue(strContains('x', 'x'));
		$this->assertFalse(strContains('', 'x'));
		$this->assertTrue(strContains("a\nb", "\n"));
	}
	
	// -------- path functions --------
	
	function testIsAbsolute() {
		$this->assertTrue(isAbsolute('/path'));
		$this->assertTrue(isAbsolute('/my/path'));
		$this->assertTrue(isAbsolute('/'));
		$this->assertTrue(isAbsolute('http://my.host'));
		$this->assertTrue(isAbsolute('svn://my.host/'));
		$this->assertTrue(isAbsolute('https://my.host/path/'));
		if (isWindows()) {
			$this->assertTrue(isAbsolute(toPath('D:\path')));
		} else {
			$this->assertFalse(isAbsolute(toPath('D:\path')), "paths with drive letter are not absolute on non-windows");
		}
		$this->assertFalse(isAbsolute('path'));
	}
	
	function testIsAbsolute_Invalid() {
		isAbsolute(null);
		$this->assertError();
	}

	function testIsAbsolute_UrlWithBackslash() {
		isAbsolute('http://my.host\path');
		$this->assertError();
		$this->assertError();
	}	
	
	function testIsRelative() {
		$this->assertTrue(isRelative('path/'));
		$this->assertTrue(isRelative('p/file'));
		$this->assertTrue(isRelative('.'));
		$this->assertTrue(isRelative(''));
		$this->assertFalse(isRelative('https://path'));
	}
	
	function testIsRelativeInvalid() {
		isRelative(123);
		$this->assertError();
	}
	
	function testIsFile() {
		$this->assertTrue(isFile('f'));
		$this->assertTrue(isFile('folder/file.txt'));
		$this->assertTrue(isFile('http://folder/file.txt'));
		$this->assertTrue(isFile('http://file.txt'), 'this method is not responsible for validating URLs');
		$this->assertFalse(isFile('file.txt/'));
	}
	
	function testIsFolder() {
		$this->assertTrue(isFolder('f/'));
		$this->assertTrue(isFolder('my/folder/'));
		$this->assertTrue(isFolder('svn://folder/'));
		$this->assertTrue(isFolder('/'));
		if (isWindows()) {
			$this->assertTrue(isFolder(toPath('C:\f\\')));
		}
		$this->assertFalse(isFolder('/path'));
	}
	
	function testGetParent() {
		$this->assertEqual('/my/', getParent('/my/folder/'));
		$this->assertEqual('/', getParent('/my/'));
		$this->assertEqual('my/', getParent('my/folder/'));
		$this->assertEqual('', getParent('my/'));
		$this->assertEqual('http://my/', getParent('http://my/file.txt'));
		if (isWindows()) {
			$this->assertEqual('C:/', getParent('C:/my/'));
		}
		getParent('');
		$this->assertError();
	}
	
	function testGetParentWindowsRoot() {
		if (isWindows()) {
			getParent('C:/');
			$this->assertError();
		}
	}
	
	function testPhpFunctions() {
		// check that PHP behaves as we expect in all OSes
		$this->assertEqual('/my/path', dirname('/my/path/file.txt'));
		$this->assertEqual('/my', dirname('/my/path/'));
		$this->assertEqual('/my', dirname('/my/path'));
		if (isWindows()) {
			$this->assertEqual('\my', dirname('\my\path'));
		}
		$this->assertEqual('', dirname(''));
		// this is quire weird behaviour
		$this->assertEqual(DIRECTORY_SEPARATOR, dirname('/'));
		// translate to generic path
		$this->assertEqual('C:/my/path/', strtr('C:\my\path\\', '\\', '/'));
	}
	
	// ------ system functions ------
	
	function testGetTempDir() {
		$dir = getTempDir();
		$this->assertTrue(strEnds($dir, DIRECTORY_SEPARATOR));
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
		$this->assertTrue(strEnds($dir1, DIRECTORY_SEPARATOR));
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
	
	// ----- command line escape functions -----
	
	function testEscapeWindowsEnv() {
		$this->assertEqual('"a%b"', escapeArgument('a%b'), 'single percent should not be a problem');
		$this->assertEqual('"a%NOT-AN-ENV-ENTRY%b"', escapeArgument('a%NOT-AN-ENV-ENTRY%b'),
			'double percent enclosing something that has not been SET should not be a problem');
		if (isWindows()) {
			$this->assertTrue(isset($_ENV['OS']), 'For this test to work OS must be an environment variable');
			$this->assertEqual('"a#OS%b"', escapeArgument('a%OS%b'));
			$this->assertEqual('"#OS%b%"', escapeArgument('%OS%b%'));
			$this->assertEqual('"#OS#OS%"', escapeArgument('%OS%OS%'));
			$this->assertEqual('"#OS%b%%cd#OS%"', escapeArgument('%OS%b%%cd%OS%'));
		}
	}
	
	// ----- portability functions -----
	
	function testIsOffline() {
		$this->assertTrue(isOffline()===!isset($_SERVER['REQUEST_URI']));
	}
	
	function testIsWindows() {
		if (DIRECTORY_SEPARATOR=='\\') {
			$this->assertTrue(isWindows());
		} else {
			$this->assertFalse(isWindows());
		}
	}
	
	// ----- url resolution functions -----
	// the tests below modify server variables, so they might affect other tests. Should maybe use simpletest mock server instead.
	
	function testGetSelfUrl() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '';
		$this->assertEqual('http://my.host', repos_getSelfUrl());
	}

	function testGetSelfUrlS() {
		$_SERVER['SERVER_PORT'] = 443;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEqual('https://my.host/', repos_getSelfUrl());
	}
	
	function testGetSelfUrlPort() {
		$_SERVER['SERVER_PORT'] = 123;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEqual('http://my.host:123/', repos_getSelfUrl());
	}

	function testGetSelfUrlPortS() {
		$_SERVER['SERVER_PORT'] = 123;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEqual('https://my.host:123/', repos_getSelfUrl());
	}

	function testGetSelfUrlFile() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/index.html';
		$this->assertEqual('http://my.host/index.html', repos_getSelfUrl());
	}
	
	function testGetSelfUrlPath() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/home/';
		$this->assertEqual('http://my.host/home/', repos_getSelfUrl());
	}
	
	function testGetSelfUrlQ() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/test/?';
		$this->assertEqual('http://my.host/test/', repos_getSelfUrl());
	}

	function testGetSelfUrlQuery() {
		$_SERVER['SERVER_PORT'] = 80;
		unset($_SERVER['HTTPS']);
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/index.html?variable';
		$this->assertEqual('http://my.host/index.html', repos_getSelfUrl());
	}

	function testGetSelfUrlQuery2() {
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
