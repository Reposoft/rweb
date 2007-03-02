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
		$this->assertEqual('xmlfile.xml', $file->getFilename());
		$this->assertEqual('/demoproject/trunk/public/', $file->getFolderPath());	
		
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
	
	function testFolder() {
		$file = new SvnOpenFile("/demoproject/trunk/public/");
		$this->assertEqual(200, $file->getStatus());
		$this->assertTrue($file->isFolder());
		$this->sendMessage("Note that isWritable should not generate an error in apache error log");
		$this->assertTrue($file->isWritable());
		$this->assertEqual('public', $file->getFilename());
		$this->assertEqual('/demoproject/trunk/', $file->getFolderPath());
	}
	
	function testFolderNoSlash() {
		$file = new SvnOpenFile("/demoproject/trunk/public");
		$this->assertEqual(301, $file->getStatus(), 
			"HTTP status should be 301, not 302, for redirect to folder with slash. %s");
		$this->assertTrue($file->isFolder(), "Should say isFolder==true when the slash is missing.");
		$this->assertTrue($file->isWritable());
	}
		
	function testFolderReadonly() {
		$file = new SvnOpenFile("/demoproject/trunk/readonly/");
		$this->assertEqual(200, $file->getStatus());
		$this->assertTrue($file->isFolder());
		$this->sendMessage("Note that isWritable should not generate an error in apache error log");
		$this->assertFalse($file->isWritable());		
	}
	
	function testFolderNoaccess() {
		$file = new SvnOpenFile("/demoproject/trunk/noaccess/");
		$this->assertEqual(403, $file->getStatus());
		$this->assertTrue($file->isFolder());
		$this->sendMessage("Note that isWritable should not generate an error in apache error log");
		$this->assertFalse($file->isWritable());		
	}
	
	function testFolderNonExisting() {
		$file = new SvnOpenFile("/demoproject/trunk/nonexisting/");
		$this->assertEqual(404, $file->getStatus());
		$this->assertTrue($file->isFolder(), "Ends with slash so it is a folder. %s");	
	}
	
}

testrun(new TestIntegrationSvnOpenFile());
?>
