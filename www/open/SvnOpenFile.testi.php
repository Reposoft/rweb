<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require("SvnOpenFile.class.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

class TestIntegrationSvnOpenFile extends UnitTestCase {
	
	function testHeadVersion() {
		$file = new SvnOpenFile("/demoproject/trunk/public/xmlfile.xml");
		$this->assertTrue($file->isLatestRevision());
		$this->assertTrue($file->isWritable());
		$this->assertTrue(is_numeric($file->getRevision()) && $file->getRevision() > 0);
		$this->assertTrue(strlen($file->getContents()) > 1, substr($file->getContents(), 0, 20).'...');
		$this->assertEqual('text/xml', $file->getType());
	}

	function testOldVersionFileExists() {
		setTestUser();
		$file = new SvnOpenFile("/demoproject/trunk/Policy document.html", 1);
		$this->assertFalse($file->isLatestRevision());
		$this->assertFalse($file->isWritable());
		$this->assertEqual(1, $file->getRevision());
		$this->assertTrue(strlen($file->getContents()) > 1, substr($file->getContents(), 0, 20).'...');
		$this->assertEqual('text/html', $file->getType());
		setTestUserNotLoggedIn();
	}
	
	function testOldVersionFileDeleted() {
		setTestUser(); // needed until REPOS-21 is solved
		$file = new SvnOpenFile("/demoproject/trunk/public/temp.txt", 1);
		$this->assertFalse($file->isLatestRevision());
		$this->assertFalse($file->isWritable());
		$this->assertEqual(1, $file->getRevision());
		$this->assertTrue(200, $file->getStatus(), "getStatus is not the HTTP status, but the equivalent. %s");
		$this->assertTrue(strlen($file->getContents()) > 1, substr($file->getContents(), 0, 20).'...');
		$this->assertEqual('text/plain', $file->getType()); // probably a guess
	}
	
	function testFileInDeletedFolder() {
		setTestUser();
		$file = new SvnOpenFile("/demoproject/trunk/old/empty.txt", 1);
		$this->assertFalse($file->isLatestRevision());
		$this->assertFalse($file->isWritable());
		$this->assertEqual(1, $file->getRevision());
		$this->assertEqual(0, $file->getSize());
		$this->assertEqual(0 ,$file->getContents());
		$this->assertEqual('text/plain', $file->getType()); // based on filename extension
	}
	
	function testLockedFile() {
		$file = new SvnOpenFile("/demoproject/trunk/public/locked-file.txt");
		$this->assertTrue($file->isLatestRevision());
		$this->assertTrue($file->isLocked());
		$this->assertTrue($file->isLockedBySomeoneElse());
		$this->assertFalse($file->isLockedByThisUser());
		$this->assertFalse($file->isWritable(),"File is not writable when locked by someone else. %s");
	}
	
}

testrun(new TestIntegrationSvnOpenFile());
?>
