<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require("System.class.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

class TestSystem extends UnitTestCase {
	
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
	
	function testPhpFunctions() {
		// check that PHP behaves as we expect in all OSes
		// dirname always removing trailing slash
		$this->assertEqual('/my/path', dirname('/my/path/file.txt'));
		$this->assertEqual('/my', dirname('/my/path/'));
		$this->assertEqual('/my', dirname('/my/path'));
		if (System::isWindows()) {
			$this->assertEqual('\my', dirname('\my\path'));
		}
		$this->assertEqual('', dirname(''));
		// this is quire weird behaviour
		$this->assertEqual(DIRECTORY_SEPARATOR, dirname('/'));
		// translate to generic path
		$this->assertEqual('C:/my/path/', strtr('C:\my\path\\', '\\', '/'));
	}
	
	function testRemoveTempDirInvalid() {
		$dir = "/this/is/any/kind/of/dir";
		System::deleteFolder($dir);
		$this->assertError();
		$this->assertError();
	}
	
	function testIsWindows() {
		if (DIRECTORY_SEPARATOR=='\\') {
			$this->assertTrue(System::isWindows());
		} else {
			$this->assertFalse(System::isWindows());
		}
	}
	
	function testGetSystemTempDir() {
		$dir = System::_getSystemTemp();
		$this->assertTrue(strEnds($dir, '/'));
		$this->assertTrue(strlen($dir)>=4, "minimal temp dir is /tmp/ or C:/tmp/");
		$this->assertTrue(file_exists($dir));
		$this->assertTrue(is_writable($dir));
		$this->assertTrue(isPath($dir));
		$this->assertTrue(isAbsolute($dir));
		$this->assertTrue(isFolder($dir));
	}
	
	function testGetTempDir() {
		$dir = System::getApplicationTemp();
		$this->assertTrue(strBegins($dir, System::_getSystemTemp()));
		$this->assertTrue(strEnds($dir, '/'));
		$this->assertTrue(strlen($dir)>=strlen(System::_getSystemTemp()), "webapp name should be appended to system temp dir");
		$this->assertTrue(file_exists($dir));
		$this->assertTrue(is_writable($dir));
		$this->assertTrue(isPath($dir));
		$this->assertTrue(isAbsolute($dir));
		$this->assertTrue(isFolder($dir));
	}

	function testGetTempDirSubfolder() {
		$dir = System::getApplicationTemp('testing');
		$this->assertTrue(strBegins($dir, System::_getSystemTemp()));
		$this->assertTrue(strEnds($dir, '/testing/'));
		$this->assertTrue(file_exists($dir));
		$this->assertTrue(is_writable($dir));
		$this->assertTrue(isPath($dir));
		$this->assertTrue(isAbsolute($dir));
		$this->assertTrue(isFolder($dir));
		rmdir($dir);
	}	
	
	function testGetTempnamDir() {
		$dir1 = System::getTempFolder();
		$dir2 = System::getTempFolder();
		$this->assertTrue(file_exists($dir1));
		$this->assertTrue(is_writable($dir1));
		$this->assertNotEqual($dir1, $dir2);
		$this->assertTrue(isAbsolute($dir1));
		$this->assertTrue(isFolder($dir1));
		// clean up
		$apptemp = System::getApplicationTemp();
		$this->assertTrue(stristr($dir1, $apptemp)==$dir1, "$dir1 should be a subfolder of app temp $apptemp");
		rmdir($dir1);
		$this->assertTrue(stristr($dir2, $apptemp)==$dir2);
		rmdir($dir2);
	}
	
	function testGetTempnamDirName() {
		$dir1 = System::getTempFolder('mytest');
		$this->assertTrue(strEnds($dir1, '/'));
		$this->assertTrue(strpos($dir1, '/mytest/')>0);
		// clean up
		$this->assertTrue(stristr($dir1, System::getApplicationTemp('mytest'))==$dir1);
		rmdir($dir1);
	}
	
	function testCreateAndDeleteFile() {
		$dir = System::getTempFolder();
		$file = $dir.'file.txt';
		System::createFile($file);
		$this->assertTrue(file_exists($file));
		System::deleteFile($file);
		$this->assertFalse(file_exists($file));
	}

	function testCreateAndDeleteFolder() {
		$dir = System::getTempFolder();
		$file = $dir.'folder/';
		System::createFolder($file);
		$this->assertTrue(file_exists($file));
		$this->assertTrue(System::deleteFolder($file), "deleteFolder returned false -> not successful");
		clearstatcache(); // needed for file_exists
		$this->assertFalse(file_exists($file), "the folder $file should have been deleted");
		$this->assertTrue(System::deleteFolder($dir), "should delete the temp folder");
	}
	
	function testGetParent() {
		$this->assertEqual('/my/', getParent('/my/folder/'));
		$this->assertEqual('/', getParent('/my/'));
		$this->assertEqual('my/', getParent('my/folder/'));
		$this->assertEqual('', getParent('my/'));
		$this->assertEqual('http://my/', getParent('http://my/file.txt'));
		if (System::isWindows()) {
			$this->assertEqual('C:/', getParent('C:/my/'));
			$this->assertFalse(getParent('C:/'), "should return false if target is windows root");
		}
		$this->assertFalse(getParent(''), "should return false if parent is undefined");
		$this->assertFalse(getParent('ftp://adsf/'), 'should return false if path is an URL and server root');
	}
	
	function testIsAbsolute() {
		$this->assertTrue(isAbsolute('/path'));
		$this->assertTrue(isAbsolute('/my/path'));
		$this->assertTrue(isAbsolute('/'));
		$this->assertTrue(isAbsolute('http://my.host'));
		$this->assertTrue(isAbsolute('svn://my.host/'));
		$this->assertTrue(isAbsolute('https://my.host/path/'));
		if (System::isWindows()) {
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
		if (System::isWindows()) {
			$this->assertTrue(isFolder(toPath('C:\f\\')));
		}
		$this->assertFalse(isFolder('/path'));
	}
	
	function testGetPathName() {
		$this->assertEqual('a.txt', getPathName('f/a.txt'));
		$this->assertEqual('a', getPathName('f/a'));
		$this->assertEqual('a', getPathName('/f/g/a'));
		$this->assertEqual('a.txt', getPathName('http://localhost/a.txt'));
		$this->assertEqual('a.txt', getPathName('a.txt'));
	}	

	function testGetPathNameFolder() {
		$this->assertEqual('ga', getPathName('fa/ga/'));
		$this->assertEqual('g', getPathName('/f/g/'));
		$this->assertEqual('f', getPathName('f/'));
		$this->assertEqual('fa', getPathName('/fa/'));
	}	
	
	function testCreateAndDeleteFileInReposWeb() {
		$dir = toPath(dirname(__FILE__));
		if (is_writable($dir)) {
		$file = $dir.'/test-file_should-be-deleted.txt';
		System::createFile($file);
		$this->assertTrue(file_exists($file));
		System::deleteFile($file);
		$this->assertFalse(file_exists($file));
		} else {
			$this->sendMessage("Can not run this test because folder '$dir' is not writable for this user.");
		}
	}
	
	function testDeleteFolderTempDir() {
		$dir = System::getTempFolder();
		System::createFolder($dir.'new folder/');
		System::createFolder($dir.'.svn/');
		System::createFile($dir.'.svn/test.txt');
		System::deleteFolder($dir);
		clearstatcache();
		$this->assertFalse(file_exists($dir.'new folder/'));
		$this->assertFalse(file_exists($dir.'.svn/test.txt'));
		$this->assertFalse(file_exists($dir.'.svn/'));
	}
	
	function testRemoveTempDirWriteProtected() {
		$dir = System::getTempFolder();
		// the svn client makes the .svn folder write protected in windows
		System::createFolder($dir.'.svn/');
		System::createFile($dir.'.svn/test.txt');
		$this->assertTrue(chmod($dir.'.svn/test.txt', 0400));
		$this->assertTrue(chmod($dir.'.svn/', 0400));
		System::deleteFolder($dir);
		//$this->assertNoErrors();
		$this->assertFalse(file_exists($dir.'.svn/test.txt'));
		$this->assertFalse(file_exists($dir.'.svn/'));
	}	

	function testDoesNotRemoveWriteProtectedUnlessInSvn() {
		// this rule does not apply to temp folder, so we'll test it here
		if (is_writable(dirname(__FILE__))) {
		$dir = toPath(dirname(__FILE__)).'/temp-test-folder-remove-anytime/';
		System::createFolder($dir);
		// the svn client makes the .svn folder write protected in windows
		System::createFolder($dir.'.sv/');
		System::createFile($dir.'.sv/test.txt');
		$this->assertTrue(chmod($dir.'.sv/test.txt', 0400));
		$this->assertTrue(chmod($dir.'.sv/', 0400));
		$this->assertFalse(System::deleteFolder($dir));
		$this->assertError();
		$this->assertError();
		$this->assertError();
		chmod($dir.'.sv/', 0700);
		chmod($dir.'.sv/test.txt', 0700);
		System::deleteFolder($dir);
		} else {
			$this->sendMessage("Can not run this test because folder '".dirname(__FILE__)."' is not writable for this user.");
		}
	}

}

testrun(new TestSystem());
?>
