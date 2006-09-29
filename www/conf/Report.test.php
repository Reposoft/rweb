<?php

require("../lib/simpletest/setup.php");

require("Report.class.php");

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
	
}

testrun(new TestReport());

?>