<?php
require("../lib/simpletest/setup.php");

require 'edit.class.php';
 
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
		$edit->result = "Committed revision 107.";
		$this->assertEqual('107', $edit->getCommittedRevision());
	}
	
	function testAddArgument() {
		$edit = new Edit('test');
		$edit->addArgPath('arg1');
		$this->assertEqual('arg1', $edit->args[0]);
		$edit->addArgOption('arg_');
		$this->assertEqual('arg1', $edit->args[0]);
		$this->assertEqual('arg_', $edit->args[1]);
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

}

$test = &new EditTest();
$test->run(new HtmlReporter());
?>