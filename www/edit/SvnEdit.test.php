<?php
require('SvnEdit.class.php');
require("../lib/simpletest/setup.php");
 
class EditTest extends UnitTestCase
{

	function testIsSuccessfulUsingDIRCommand() {
		$edit = new SvnEdit('test');
		// most systems support the 'dir' command
		exec('dir', $output, $edit->returnval);
		$this->assertTrue($edit->isSuccessful());
	}
	
	function testIsSuccessfulFalse() {
		$edit = new SvnEdit('test');
		// most systems support the 'dir' command
		exec('thiscommanddoesnotexist', $output, $edit->returnval);
		$this->assertFalse($edit->isSuccessful());
	}

	function testIsSuccessfulZero() {
		$edit = new SvnEdit('test');
		$edit->returnval = 0;
		$this->assertTrue($edit->isSuccessful());
	}

	function testExtractRevision() {
		$edit = new SvnEdit('test');
		$this->returnval = true;
		$edit->output = array("Committed revision 107.");
		$this->assertEqual('107', $edit->getCommittedRevision());
	}
	
	/**
	 * Only some of the command arguments should be escaped, so escaping must be done per argument type.
	 */
	function testAddArgument() {
		$edit = new SvnEdit('test');
		$edit->addArgPath('arg1');
		$this->assertEqual('"arg1"', $edit->args[0]);
		$edit->addArgOption('arg_');
		$this->assertEqual('"arg1"', $edit->args[0]);
		$this->assertEqual('arg_', $edit->args[1]);
		// file and path should only be escaped, not converted with toPath or toShellEncoding
		$edit->addArgPath('\temp\file.txt');
		$edit->addArgFilename("file\xc3\xa5.txt");
		$this->assertEqual("\"\\\\temp\\\\file.txt\"", $edit->args[2]);
		$this->assertEqual("\"file\xc3\xa5.txt\"", $edit->args[3]);
	}
	
	function testPercentInFilename(){
		$edit = new SvnEdit('test');
		$edit->addArgUrl('http://www.where-we-work.com/%procent%');
		$this->assertEqual('"http://www.where-we-work.com/%25procent%25"', $edit->args[0]);
	}
	
	function testCommand() {
		// actually we shouldnt care much about what the command looks like, but here's one test to help
		$edit = new SvnEdit('import');
		$edit->setMessage('msg');
		$edit->addArgFilename('file.txt');
		$edit->addArgUrl('https://my.repo/file.txt');
		$cmd = $edit->getCommand();
		$this->assertEqual('import -m "msg" "file.txt" "https://my.repo/file.txt"', $cmd);
	}
	
	function testCommandEscape() {
		if (substr(PHP_OS, 0, 3) != 'WIN') {
			$edit = new SvnEdit('" $(ls)');
			$edit->setMessage('msg " `ls` \'ls\' \ " | rm');
			$cmd = $edit->getCommand();
			$this->assertEqual('\" \$\(ls\) -m "msg \" \`ls\` \'ls\' \\\\ \" | rm"', $cmd);
		}
	}
	
	function testNewFilenameRule() {
		$r = new NewFilenameRule('test', '/test/trunk/');
		$this->assertEqual('/test/trunk/a.txt', $r->_getPath('a.txt'));
	}
	
	function testGetResourceTypeNonExisting() {
		$url = '/demoproject/trunk/public/does-not-exist-adsferqwerw/';
		$this->assertEqual(0, login_getResourceType($url));
		$this->assertTrue(login_getResourceType($url)==false);
		$this->assertFalse(login_getResourceType($url)===false);
	}

	// ---- tests below are for the homeless getResourceType ----
	
	function testGetResourceTypeFolder() {
		$url = '/demoproject/trunk/public/';
		$this->assertEqual(1, login_getResourceType($url));
		$this->assertTrue(login_getResourceType($url)==true); // it does exist
		$this->assertFalse(login_getResourceType($url)===true); // never returns boolean true
	}

	function testGetResourceTypeFolderNoSlash() {
		$url = '/demoproject/trunk/public'; // must be able to accept folders without tailing slash (for svn log --xml output)
		$this->assertEqual(1, login_getResourceType($url));
		$this->assertTrue(login_getResourceType($url)==true); // it does exist
		$this->assertFalse(login_getResourceType($url)===true); // never returns boolean true
	}
	
	function testGetResourceTypeFolderWithTestUser() {
		if (isLoggedIn() && getReposUser()=='test') {
		$url = '/test/trunk';
		$this->assertEqual(1, login_getResourceType($url));
		} else {
			$this->sendMessage("test user is not logged in, so this test is skipped");
		}
	}

}

testrun(new EditTest());
?>