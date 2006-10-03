<?php

require("../lib/simpletest/setup.php");

// the test reporter also uses this class, so here we need require_once
require_once("Report.class.php");

// override the real class with custom behaviours for test
class SaveReport extends Report {
	var $printed = '';
	// override _print method
	function _print($string) {
		$this->printed .= $string;
	}
}

class TestReport extends UnitTestCase {
	
	var $report;
	
	function setUp() {
		$this->report = new SaveReport('test report');
		// check that the title is in the header
		$this->assertTrue(strpos($this->report->printed, 'test report')>0);
		$this->report->printed = '';
	}
	
	function testInitialize() {
		// want an empty output buffer after setup
		$this->assertTrue(strlen($this->report->printed)==0);
	}
	
	function testOutput() {
		$this->report->_output('test string');
		$this->assertEqual('test string', $this->report->printed);
	}

	function testOutputArray() {
		$this->report->_output(array("string1"));
		$rows = explode("\n",$this->report->printed);
		$this->assertTrue(2<count($rows), "Block output is minimum 3 lines");
		$this->assertTrue('test string', $rows[1]);
	}

	function testHasErrors() {
		$this->report->warn('warn');
		$this->assertFalse($this->report->hasErrors());
		$this->report->error('error');
		$this->assertTrue($this->report->hasErrors());
	}

	function testHasErrorsFail() {
		$this->report->fail('fail');
		$this->assertTrue($this->report->hasErrors());
	}
	
	function testOkMessage() {
		$this->report->ok();
		$this->assertEqual(1, $this->report->no, "should count test pass");
		$this->assertTrue(strContains($this->report->printed, '='));
	}
}

testrun(new TestReport());

?>