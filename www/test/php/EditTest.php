<?php
require_once 'PHPUnit/Framework/TestCase.php';

require '../../edit/edit.class.php';
 
class EditTest extends PHPUnit_Framework_TestCase
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
		$this->assertEquals('107', $edit->getCommittedRevision());
	}
	
	function testAddArgument() {
		$edit = new Edit('test');
		$edit->addArgument('arg1');
		$this->assertEquals('arg1', $edit->args[0]);
		$edit->addArgument('arg_');
		$this->assertEquals('arg1', $edit->args[0]);
		$this->assertEquals('arg_', $edit->args[1]);
	}

}
?>