<?php
require('edit.class.php');
require("../lib/simpletest/setup.php");
 
class EditTest extends UnitTestCase
{

	function testIsSuccessfulUsingDIRCommand() {
		$edit = new Edit('test');
		// most systems support the 'dir' command
		exec('dir', $output, $edit->returnval);
		$this->assertTrue($edit->isSuccessful());
	}
	
	function testIsSuccessfulFalse() {
		$edit = new Edit('test');
		// most systems support the 'dir' command
		exec('thiscommanddoesnotexist', $output, $edit->returnval);
		$this->assertFalse($edit->isSuccessful());
	}

	function testIsSuccessfulZero() {
		$edit = new Edit('test');
		$edit->returnval = 0;
		$this->assertTrue($edit->isSuccessful());
	}

	function testExtractRevision() {
		$edit = new Edit('test');
		$this->returnval = true;
		$edit->output = array("Committed revision 107.");
		$this->assertEqual('107', $edit->getCommittedRevision());
	}
	
	/**
	 * Only some of the command arguments should be escaped, so escaping must be done per argument type.
	 */
	function testAddArgument() {
		$edit = new Edit('test');
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
		$edit = new Edit('test');
		$edit->addArgUrl('http://www.where-we-work.com/%procent%');
		$this->assertEqual('"http://www.where-we-work.com/%25procent%25"', $edit->args[0]);
	}
	
	function testCommand() {
		// actually we shouldnt care much about what the command looks like, but here's one test to help
		$edit = new Edit('import');
		$edit->setMessage('msg');
		$edit->addArgFilename('file.txt');
		$edit->addArgUrl('https://my.repo/file.txt');
		$cmd = $edit->getCommand();
		$this->assertEqual('import -m "msg" "file.txt" "https://my.repo/file.txt"', $cmd);
	}
	
	function testCommandEscape() {
		if (substr(PHP_OS, 0, 3) != 'WIN') {
			$edit = new Edit('" $(ls)');
			$edit->setMessage('msg " `ls` \'ls\' \ " | rm');
			$cmd = $edit->getCommand();
			$this->assertEqual('\" \$\(ls\) -m "msg \" \`ls\` \'ls\' \\\\ \" | rm"', $cmd);
		}
	}
	
	function testNewFilenameRule() {
		$r = new NewFilenameRule('test', '/test/trunk/');
		$this->assertEqual('/test/trunk/a.txt', $r->_getPath('a.txt'));
	}

}

testrun(new EditTest());
?>