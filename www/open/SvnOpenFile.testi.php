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
		$this->assertEqual('', $file->getLockComment());
		$this->assertTrue(is_numeric($file->getRevision()) && $file->getRevision() > 0);
		$this->assertEqual('text/xml', $file->getType());
		
		$contents = $file->getContents();
		$this->assertEqual(">\n", substr($contents, strlen($contents)-2), "File ends with newline. %s");
		$contentsArray = $file->getContentsText();
		$this->assertEqual(2, count($contentsArray), "The last line is empty, should be in the array. %s");
		
		// test passthru (sendInlineHtml), sendInline is just a plain passthru, so it is not tested
		ob_start();
		ob_flush();
		$file->sendInlineHtml();
		$inline = ob_get_clean();
		$this->assertEqual("&gt;\n", substr($inline, strlen($inline)-5), "%s");
	}

	function testHeadVersionReadonly() {
		setTestUser();
		$file = new SvnOpenFile("/demoproject/trunk/readonly/index.html");
		$this->assertTrue($file->isLatestRevision());
		$this->assertFalse($file->isWritable(), "The file is in a readonly folder. %2");
		$this->assertEqual(1, $file->getRevision(), "Shouldn't have been changed since first commit. %s");
		$this->assertEqual('text/plain', $file->getType(), "Content type not explicitly set. %s"); 
		$contents = $file->getContents();
		$this->assertEqual("</html>\n", substr($contents, strlen($contents)-8), "File ends with newline. %s");
		$contentsArray = $file->getContentsText();
		$this->assertEqual(2, count($contentsArray), "The last line is empty, should be in the array. %s");
	}
	
	function testGetContentsBinary() {
		$file = new SvnOpenFile("/demoproject/trunk/public/images/a.gif");
		$this->assertTrue($file->isLatestRevision());
		$this->assertTrue($file->isWritable());
		$this->assertFalse(strEnds($file->getContents(), "\n"));
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
	
	function testNonexistingFileHead() {
		$file = new SvnOpenFile("/demoproject/trunk/public/thispathdoesnotexist.tmp");
		$this->assertEqual(404, $file->getStatus());
		$this->assertFalse($file->isWritable());
	}
	
	function testNonexistingFile() {
		$file = new SvnOpenFile("/demoproject/trunk/public/temp.txt", 2);
		$this->assertEqual(404, $file->getStatus());
		$this->assertFalse($file->isWritable());
	}
	
	function testOldVersionFileDeleted() {
		setTestUser(); // needed until REPOS-21 is solved
		$file = new SvnOpenFile("/demoproject/trunk/public/temp.txt", 1);
		$this->assertFalse($file->isLatestRevision());
		$this->assertFalse($file->isWritable());
		$this->assertEqual(1, $file->getRevision());
		$this->assertTrue(200, $file->getStatus(), "getStatus is not the HTTP status, but the equivalent. %s");
		$this->assertEqual(MIMETYPE_UNKNOWN, $file->getType());
		// this file does not end with newline
		$contents = $file->getContents();
		$this->assertTrue(strlen($contents) > 0);
		$this->assertNotEqual("\n", substr($contents, strlen($contents)));
		$contentsArray = $file->getContentsText();
		$this->assertEqual(1, count($contentsArray), "Should be only one line in array. %s");
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
