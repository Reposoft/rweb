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
		$this->assertTrue(strEnds('http://localhost/repos/plugins/validation/Validation.test.php', 'validation/Validation.test.php'));
	}
	
	function testStrContains() {
		$this->assertTrue(strContains('xy', 'x'));
		$this->assertFalse(strContains('x', 'xy'));
		$this->assertTrue(strContains('x', 'x'));
		$this->assertFalse(strContains('', 'x'));
		$this->assertTrue(strContains("a\nb", "\n"));
	}
	
	function testStrAfter() {
		$this->assertEqual('hej&ho', strAfter('hej/?t=hej&ho', '?t='));
	}
	
	// -------- configuration -------- 
	
	function testGetWebapp() {
		$this->assertTrue(strEnds(getWebapp(),'/repos/'), "Currently repos must be installed in /repos/");
	}
	
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
	
	// -------- path functions --------
	
	function testUrlEncodeNames() {
		$this->assertEqual('https://host/r%25p', urlEncodeNames('https://host/r%p'));
		$this->assertEqual('http://host:80/r%22/?s%25t', urlEncodeNames('http://host:80/r"/?s%t'));
		$this->assertEqual("/r%3As/t%20/?u%3F", urlEncodeNames("/r:s/t /?u?"));
	}
	
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
	
	function test() {
		$this->assertEqual('/my/', getParent('/my/folder/'));
		$this->assertEqual('/', getParent('/my/'));
		$this->assertEqual('my/', getParent('my/folder/'));
		$this->assertEqual('', getParent('my/'));
		$this->assertEqual('http://my/', getParent('http://my/file.txt'));
		if (isWindows()) {
			$this->assertEqual('C:/', getParent('C:/my/'));
			$this->assertFalse(getParent('C:/'), "should return false if target is windows root");
		}
		$this->assertFalse(getParent(''), "should return false if parent is undefined");
		$this->assertFalse(getParent('ftp://adsf/'), 'should return false if path is an URL and server root');
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

	function testGetSystemTempDir() {
		$dir = getSystemTempDir();
		$this->assertTrue(strEnds($dir, '/'));
		$this->assertTrue(strlen($dir)>=4, "minimal temp dir is /tmp/ or C:/tmp/");
		$this->assertTrue(file_exists($dir));
		$this->assertTrue(is_writable($dir));
		$this->assertTrue(isPath($dir));
		$this->assertTrue(isAbsolute($dir));
		$this->assertTrue(isFolder($dir));
	}
	
	function testGetTempDir() {
		$dir = getTempDir();
		$this->assertTrue(strBegins($dir, getSystemTempDir()));
		$this->assertTrue(strEnds($dir, '/'));
		$this->assertTrue(strlen($dir)>=strlen(getSystemTempDir()), "webapp name should be appended to system temp dir");
		$this->assertTrue(file_exists($dir));
		$this->assertTrue(is_writable($dir));
		$this->assertTrue(isPath($dir));
		$this->assertTrue(isAbsolute($dir));
		$this->assertTrue(isFolder($dir));
	}

	function testGetTempDirSubfolder() {
		$dir = getTempDir('testing');
		$this->assertTrue(strBegins($dir, getSystemTempDir()));
		$this->assertTrue(strEnds($dir, '/testing/'));
		$this->assertTrue(file_exists($dir));
		$this->assertTrue(is_writable($dir));
		$this->assertTrue(isPath($dir));
		$this->assertTrue(isAbsolute($dir));
		$this->assertTrue(isFolder($dir));
		rmdir($dir);
	}	
	
	function testGetTempnamDir() {
		$dir1 = getTempnamDir();
		$dir2 = getTempnamDir();
		$this->assertTrue(file_exists($dir1));
		$this->assertTrue(is_writable($dir1));
		$this->assertNotEqual($dir1, $dir2);
		$this->assertTrue(isAbsolute($dir1));
		$this->assertTrue(isFolder($dir1));
		// clean up
		$this->assertTrue(strBegins($dir1, getTempDir()));
		rmdir($dir1);
		$this->assertTrue(strBegins($dir2, getTempDir()));
		rmdir($dir2);
	}
	
	function testGetTempnamDirName() {
		$dir1 = getTempnamDir('mytest');
		$this->assertTrue(strEnds($dir1, '/'));
		$this->assertTrue(strpos($dir1, '/mytest/')>0);
		// clean up
		$this->assertTrue(strBegins($dir1, getTempDir()));
		rmdir($dir1);
	}
	
	function testCreateAndDeleteFile() {
		$dir = getTempnamDir();
		$file = $dir.'file.txt';
		createFile($file);
		$this->assertTrue(file_exists($file));
		deleteFile($file);
		$this->assertFalse(file_exists($file));
	}

	function testCreateAndDeleteFolder() {
		$dir = getTempnamDir();
		$file = $dir.'folder/';
		createFolder($file);
		$this->assertTrue(file_exists($file));
		$this->assertTrue(deleteFolder($file), "deleteFolder returned false -> not successful");
		clearstatcache(); // needed for file_exists
		$this->assertFalse(file_exists($file), "the folder $file should have been deleted");
		$this->assertTrue(deleteFolder($dir), "should delete the temp folder");
	}
	
	function testCreateAndDeleteFileInReposWeb() {
		$dir = toPath(dirname(__FILE__));
		if (is_writable($dir)) {
		$file = $dir.'/test-file_should-be-deleted.txt';
		createFile($file);
		$this->assertTrue(file_exists($file));
		deleteFile($file);
		$this->assertFalse(file_exists($file));
		} else {
			$this->sendMessage("Can not run this test because folder '$dir' is not writable for this user.");
		}
	}
	
	function testDeleteFolderTempDir() {
		$dir = getTempnamDir();
		createFolder($dir.'new folder/');
		createFolder($dir.'.svn/');
		createFile($dir.'.svn/test.txt');
		deleteFolder($dir);
		clearstatcache();
		$this->assertFalse(file_exists($dir.'new folder/'));
		$this->assertFalse(file_exists($dir.'.svn/test.txt'));
		$this->assertFalse(file_exists($dir.'.svn/'));
	}
	
	function testRemoveTempDirWriteProtected() {
		$dir = getTempnamDir();
		// the svn client makes the .svn folder write protected in windows
		createFolder($dir.'.svn/');
		createFile($dir.'.svn/test.txt');
		$this->assertTrue(chmod($dir.'.svn/test.txt', 0400));
		$this->assertTrue(chmod($dir.'.svn/', 0400));
		deleteFolder($dir);
		//$this->assertNoErrors();
		$this->assertFalse(file_exists($dir.'.svn/test.txt'));
		$this->assertFalse(file_exists($dir.'.svn/'));
	}	

	function testDoesNotRemoveWriteProtectedUnlessInSvn() {
		// this rule does not apply to temp folder, so we'll test it here
		if (is_writable(dirname(__FILE__))) {
		$dir = toPath(dirname(__FILE__)).'/temp-test-folder-remove-anytime/';
		createFolder($dir);
		// the svn client makes the .svn folder write protected in windows
		createFolder($dir.'.sv/');
		createFile($dir.'.sv/test.txt');
		$this->assertTrue(chmod($dir.'.sv/test.txt', 0400));
		$this->assertTrue(chmod($dir.'.sv/', 0400));
		$this->assertFalse(deleteFolder($dir));
		$this->assertError();
		$this->assertError();
		$this->assertError();
		chmod($dir.'.sv/', 0700);
		chmod($dir.'.sv/test.txt', 0700);
		deleteFolder($dir);
		} else {
			$this->sendMessage("Can not run this test because folder '".dirname(__FILE__)."' is not writable for this user.");
		}
	}	
	
	function testRemoveTempDirInvalid() {
		$dir = "/this/is/any/kind/of/dir";
		deleteFolder($dir);
		$this->assertError();
		$this->assertError();
	}
	
	// ----- command line escape functions -----
	
	function testEscapeArgument() {
		// common escape rules
		$this->assertEqual("\"a\\\\b\"", escapeArgument('a\b'));
		// rules that depend on OS
		if (!isWindows()) {
			$this->assertEqual('"a\"b"', escapeArgument('a"b'));	
		}
		if (isWindows()) {
			$this->assertEqual('"a""b"', escapeArgument('a"b'));	
		}
	}
	
	function testEscapeWindowsEnv() {
		$this->assertEqual('"a%b"', escapeArgument('a%b'), 'single percent should not be a problem');
		$this->assertEqual('"a%NOT-AN-ENV-ENTRY%b"', escapeArgument('a%NOT-AN-ENV-ENTRY%b'),
			'double percent enclosing something that has not been SET should not be a problem');
		if (isWindows()) {
			$this->assertTrue(getenv('OS'), 'For this test to work OS must be an environment variable');
			$this->assertEqual('"a%OS#b"', escapeArgument('a%OS%b'));
			$this->assertEqual('"%OS#b%"', escapeArgument('%OS%b%'));
			$this->assertEqual('"%O%OS#b%"', escapeArgument('%O%OS%b%'));
			$this->assertEqual('"%OS#OS%"', escapeArgument('%OS%OS%'));
			$this->assertEqual('"%OS#b%%cd%OS#"', escapeArgument('%OS%b%%cd%OS%'));
		}
	}
	
	function testGetScriptWrapper() {
		if (!isWindows()) {
			$this->assertTrue(file_exists(_repos_getScriptWrapper()), "the script wrapper file "._repos_getScriptWrapper()." does not exist");
		}
	}
	
	function testExclamtionMarkInPrompt() {
		// in interactive mode in bash, exclamation marks must be escaped
		// but \ does not work (it is stored with the log message) so the best option is probably "$'\x21'"
		// but, hopefully we don't run in interactive mode
		$out = null;
		$return = null;
		$v = exec('echo test!ing', $out, $return);
		$this->assertEqual(0, $return);
		$this->assertEqual('test!ing', $v);
	}
	
	function testCommandLineEncoding() {
		
		// plain ascii
		$v = exec("echo nada");
		$this->assertEqual("nada", $v);
		if (isWindows()) { // don't know why this test does not work in windows
			// latin-1
			$v = exec("echo n\xE4d\xE5");
			$this->assertEqual("n\xE4d\xE5", $v);
			$this->assertEqual("6e e4 64 e5 ", $this->getchars($v));
		} else {
			// utf-8
			$v = exec("echo n\xc3\xa4d\xc3\xa5");
			$this->assertEqual("n\xc3\xa4d\xc3\xa5", $v);
			$this->assertEqual("6e c3 a4 64 c3 a5 ", $this->getchars($v));
		}
	}
	
	function getchars($string) {
		$c = "";
		for ($i=0;$i<strlen($string);$i++) {
	   	$chr = $string{$i};
	   	$ord = ord($chr);
	   	$c .= dechex($ord)." ";
		}
		return $c;
	}
	
	// ----- portability functions -----
	
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

testrun(new TestReposProperties());

?>
