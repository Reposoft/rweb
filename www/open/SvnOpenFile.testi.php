<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require("SvnOpenFile.class.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");
// rely on SvnEdit for setup in some test cases
require("../edit/SvnEdit.class.php");

class TestIntegrationSvnOpenFile extends UnitTestCase {
	
	function setUp() {
		_svnOpenFile_setInstance(null);
	}	
	
	function testHeadVersion() {
		setTestUser();
		$file = new SvnOpenFile("/demoproject/trunk/public/xmlfile.xml");
		$this->assertTrue($file->isLatestRevision());
		$this->assertTrue($file->isWritable());
		$this->assertTrue($file->isWriteAllow());
		$this->assertEqual('', $file->getLockComment());
		$this->assertTrue(is_numeric($file->getRevision()) && $file->getRevision() > 0);
		$this->assertEqual('text/xml', $file->getType());
		$this->assertEqual('xmlfile.xml', $file->getFilename());
		$this->assertEqual('/demoproject/trunk/public/', $file->getFolderPath());
		$this->assertEqual('file', $file->getKind());	
		
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
	
	function testHeadVersionAnonymousUser() {
		setTestUserNotLoggedIn();
		$file = new SvnOpenFile("/demoproject/trunk/public/xmlfile.xml");
		$this->assertFalse($file->isWritable(),
			"File may be writable in SVN, but if the user is logged in we don't know, and we'd rather say no. %s");
	}

	function testHeadVersionReadonly() {
		setTestUser();
		$file = new SvnOpenFile("/demoproject/trunk/readonly/index.html");
		$this->assertTrue($file->isLatestRevision());
		$this->assertFalse($file->isWritable(), "The file is in a readonly folder. %2");
		$this->assertEqual(1, $file->getRevision(), "Shouldn't have been changed since first commit. %s");
		$this->assertEqual('text/plain', $file->getType(), "mime-type property not set so content type should be deteced by apache. %s"); 
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
	}
	
	function testNonexistingFileHead() {
		setTestUser();
		$file = new SvnOpenFile("/demoproject/trunk/public/thispathdoesnotexist.tmp");
		$this->assertEqual(404, $file->getStatus());
		// Why support this? // $this->assertEqual('HEAD', $file->getRevision(), "No revision can be found. %s");
		$this->assertFalse($file->isWritable());
	}
	
	function testNonexistingFile() {
		$file = new SvnOpenFile("/demoproject/trunk/public/temp.txt", 2);
		//$this->expectError(new PatternExpectation("/Could not read file .* from svn/"));
		$this->assertEqual(404, $file->getStatus());
		// Why support this? // $this->assertEqual(2, $file->getRevision(),
		//	 "Revision not found in svn, so return the value given to the constructor. %s");
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
		//$this->assertEqual('text/plain', $file->getType()); // based on filename extension
		$this->assertEqual(MIMETYPE_UNKNOWN, $file->getType(),
			"until MIME guessing is implemented, we don't know type if the file does not exist in apache. %s");
	}
	
	function testLockedFile() {
		setTestUser();
		$file = new SvnOpenFile("/demoproject/trunk/public/locked-file.txt");
		$this->assertTrue($file->isLatestRevision());
		$this->assertTrue($file->isLocked());
		$this->assertTrue($file->isLockedBySomeoneElse());
		$this->assertFalse($file->isLockedByThisUser());
		//$this->assertFalse($file->isWritable(),"File is not writable when locked by someone else. %s");
		// for performance reasons we don't want to require an svn call to check writable
		$this->assertTrue($file->isWritable(),"Even if it is locked it is still writable. %s");
		// requires a user
		$this->assertFalse($file->isWriteAllow(), "Current user not allowed to write to this file");
		// TODO test as user svensson
		// TODO test as user not logged in
	}
	
	function testFileNoExtension() {
		$file = new SvnOpenFile("/test/trunk/TESTACCOUNT");
		$this->assertEqual(200, $file->getStatus());
		$this->assertEqual('file', $file->getKind());
	}
	
	function testFileNoExtensionRev() {
		$revWhenAdded = 6; 
		$file = new SvnOpenFile("/test/trunk/TESTACCOUNT", $revWhenAdded);
		$this->assertEqual(200, $file->getStatus());
		$this->assertEqual('file', $file->getKind());
	}

	function testFileNoExtensionRevNotInHead() {
		// setup
		$target = '/test/trunk/TESTTEMP';
		$url = getRepository().$target;
		$tmp = System::getTempFile('test','');
		$import = new SvnEdit('import');
		$import->addArgFilename($tmp);
		$import->addArgUrl($url);
		if ($import->execNoDisplay()) {
			$this->fail('Failed to import test file');
			$this->sendMessage($import->getOutput());
			return;
		}
		
		$revWhenAdded = $import->getCommittedRevision();
		$delete = new SvnEdit('rm');
		$delete->setMessage('testFileNoExtensionRevNotInHead');
		$delete->addArgUrl($url);
		$this->assertFalse($delete->exec(),'delete test file');
		// test
		$file = new SvnOpenFile($target, $revWhenAdded);
		$this->assertEqual(200, $file->getStatus());
		$this->assertEqual('file', $file->getKind());
		// cleanp
		System::deleteFile($tmp);
	}
	
	function testFolder() {
		$file = new SvnOpenFile("/demoproject/trunk/public/");
		$this->assertEqual(200, $file->getStatus());
		$this->assertTrue($file->isFolder());
		$this->sendMessage("Note that isWritable should not generate an error in apache error log");
		$this->assertTrue($file->isWritable());
		$this->assertEqual('public', $file->getFilename());
		$this->assertEqual('/demoproject/trunk/', $file->getFolderPath());
		$this->assertEqual('dir', $file->getKind(), "%s");
		$this->assertEqual('folder', $file->getKind2(), "getKind2 is for user friendly name. %s");
		// not allowed //$this->assertNotEqual(HEAD, $file->getRevision(), "Should get the real revision number. %s");
	}
	
	function testFolderEmpty() {
		$file = new SvnOpenFile("/test/trunk/trunk/trunk/");
		$this->assertEqual(200, $file->getStatus());
		$this->assertTrue($file->isFolder());
		$this->assertEqual('trunk', $file->getFilename());
		// not allowed //$this->assertNotEqual(HEAD, $file->getRevision(), "Should get the real revision number. %s");
	}
	
	function testFolderNoSlash() {
		$file = new SvnOpenFile("/demoproject/trunk/public");
		$this->assertEqual(301, $file->getStatus(), 
			"HTTP status should be 301, not 302, for redirect to folder with slash. %s");
		$this->assertTrue($file->isFolder(), "Should say isFolder==true when the slash is missing.");
		$this->assertTrue($file->isWritable());
		$this->assertFalse($file->isLocked(), "Folders are never locked, even if they contain a locked file. %s");
		$this->assertFalse($file->isLockedBySomeoneElse(), "Folders are never locked, even if they contain a locked file. %s");
		$this->assertFalse($file->isLockedByThisUser(), "Folders are never locked, even if they contain a locked file. %s");
	}
		
	function testFolderReadonly() {
		$file = new SvnOpenFile("/demoproject/trunk/readonly/");
		$this->assertEqual(200, $file->getStatus());
		$this->assertTrue($file->isFolder());
		$this->sendMessage("Note that isWritable should not generate an error in apache error log");
		$this->assertFalse($file->isWritable());	
		$this->assertFalse($file->isLocked(), "Folders are never locked. %s");	
	}
	
	function testFolderNoaccess() {
		$file = new SvnOpenFile("/demoproject/trunk/noaccess/");
		$this->assertEqual(403, $file->getStatus());
		$this->assertTrue($file->isFolder());
		$this->sendMessage("Note that isWritable should not generate an error in apache error log");
		$this->assertFalse($file->isWritable());
		$this->assertFalse($file->isLocked(), "Folders are never locked. %s");
	}
	
	function testFolderNonExisting() {
		$file = new SvnOpenFile("/demoproject/trunk/nonexisting/");
		$this->assertEqual(404, $file->getStatus());
		$this->assertTrue($file->isFolder(), "Ends with slash so it is a folder. %s");	
	}
	
	function testFolderDeleted() {
		setTestUser();
		$file = new SvnOpenFile("/demoproject/trunk/old/", 1);
		$this->assertTrue($file->isFolder());
		$this->assertFalse($file->isLatestRevision());
		$this->assertFalse($file->isReadableInHead());
		// not allowed //$this->assertEqual(1, $file->getRevision());
	}

	function testFolderOldVersion() {
		setTestUser();
		$file = new SvnOpenFile("/demoproject/trunk/", 1);
		$this->assertTrue($file->isFolder());
		$this->assertTrue($file->isReadableInHead());
		$this->expectError(new PatternExpectation('/could not read.* revision/i',
			"isLatedRevision is not supported for folders, unless answer is obvious. %s"));
		$this->assertFalse($file->isLatestRevision());
		// not allowed //$this->assertEqual(1, $file->getRevision());
	}	
	
	function testFolderDeletedNoSlash() {
		setTestUser();
		// No trailing slash, but it is impossible for target login to do redirect to folder,
		// because it can not know that it is a folder using an HTTP call.
		// This means we have to handle it like it is.
		$file = new SvnOpenFile("/demoproject/trunk/old", 1);
		$this->assertTrue($file->isFolder());
		$this->assertFalse($file->isLatestRevision());
		$this->assertFalse($file->isReadableInHead());
	}
	
	function testFolderDeletedDotInName() {
		// setup
		$target = '/test/trunk/branch1.2';
		$url = getRepository().$target;
		$tmp = System::getTempFolder('test','');
		$import = new SvnEdit('import');
		$import->addArgFilename($tmp);
		$import->addArgUrl($url);
		if ($import->execNoDisplay()) {
			$this->fail('Failed to import test folder');
			$this->sendMessage($import->getOutput());
		}
		$revWhenAdded = $import->getCommittedRevision();
		$delete = new SvnEdit('rm');
		$delete->setMessage('testFolderDeletedDotInName');
		$delete->addArgUrl($url);
		$this->assertFalse($delete->exec(),'delete folder file');
		// test
		$file = new SvnOpenFile($target, $revWhenAdded);
		$this->assertEqual(200, $file->getStatus());
		$this->assertEqual('dir', $file->getKind());
		// cleanp
		System::deleteFolder($tmp);
	}
	
	function testFileInRenamedFolderNotEdited() {
		setTestUser();
		// we have seen problems with peg revisions inside a renamed folder, when the file has not been modified
		// (which makes the revision number of the file lower than the revision number of the folder)
		$file = new SvnOpenFile("/demoproject/trunk/public/website/images/house.svg");
		$contents = $file->getContents();
		$this->assertTrue(strBegins($contents, '<?xml'), "%s Got: ".htmlspecialchars(substr($contents,0,40)));
	}
	
}

testrun(new TestIntegrationSvnOpenFile());
?>
