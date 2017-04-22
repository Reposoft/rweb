<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require('xmlConflictHandler.php');
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../../lib/simpletest/setup.php");

class TestXmlConflictHandler extends UnitTestCase {

	function setUp() {
		
	}
	
	function testConflict() {
		$c = new Conflict(1);
		$c->setLimitLine(5);
		$c->setEndLine(9);
		$this->assertEqual(5, $c->getLimitLine());
	}
	
	function testFindConflicts() {
		$data = '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
  <Author>Svensson</Author>
<<<<<<< .working
working
=======
merge
>>>>>>> .merge-right.r17
  <Created>2006-11-24T14:13:43Z</Created>';
		$array = explode("\n", $data);	
		$conflicts = findConflict($array, $log);
		$this->dump(null, $log);
		$this->assertEqual(1, count($conflicts));
		$c = $conflicts[0];
		$this->assertEqual(3, $c->getStartLine());
		$this->assertEqual(5, $c->getLimitLine());
		$this->assertEqual(7, $c->getEndLine());
		$w = $c->getWorking();
		$this->dump(null, $w);
		$this->assertEqual(1, count($w));
		$this->assertEqual($array[4], $w[0]);
		$w = $c->getMerge();
		$this->dump(null, $w);
		$this->assertEqual(1, count($w));
		$this->assertEqual($array[6], $w[0]);
	}
	
	function testmarkAutoResolve_TypeNoTypeSet() {
		$c = new Conflict(0);
		$c->working = array('mine checked out from trunk');
		$c->merge = array('from branch');
		// no type set
		
		markAutoResolve($c);
		
		$this->assertTrue($c->isResolved());
		$this->assertEqual(array('from branch'), $c->getResolvedLines());
	}
	
	function testmarkAutoResolve_TypeTable() {
		$c = new Conflict(0);
		$c->working = array('mine checked out from trunk');
		$c->merge = array('from branch');
		$c->setType(CONFLICT_EXCEL_TABLE);
		
		markAutoResolve($c);
		
		$this->assertFalse($c->isResolved(), "generally we can not solve conflict in excel table");
	}
	
	function testmarkLastAuthor() {
		$c = new Conflict(0);
		$c->working = array('  <LastAuthor>test</LastAuthor>');
		$c->merge = array('  <LastAuthor>bamse</LastAuthor>');
		
		markAutoResolve($c);
		
		$this->assertTrue($c->isResolved());
		$this->assertEqual(array('  <LastAuthor>bamse</LastAuthor>'), $c->getResolvedLines());
	}

	function testmarkCompany() {
		$c = new Conflict(0);
		$c->working = array('  <Company>A</Company>');
		$c->merge = array('  <Company>B</Company>');
		
		markAutoResolve($c);
		
		$this->assertTrue($c->isResolved());
		$this->assertEqual(array('  <Company>A</Company>'), $c->getResolvedLines());
	}
	
	function testmarkIdenticalFunctions() {
		$c = new Conflict(0);
		$c->setType(CONFLICT_EXCEL_TABLE);
		$c->working = array('<Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>');
		$c->merge = array('<Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">5</Data></Cell>');
		
		markAutoResolve($c);
		
		$this->assertTrue($c->isResolved());
		$this->assertEqual(array('<Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>'), $c->getResolvedLines());
	}
	
	function testmarkModifiedFunctions() {
		$c = new Conflict(0);
		$c->setType(CONFLICT_EXCEL_TABLE);
		$c->working = array('<Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>');
		$c->merge = array('<Cell ss:Formula="=R[-3]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>');
		
		markAutoResolve($c);
		
		$this->assertFalse($c->isResolved());
	}
}

testrun(new TestXmlConflictHandler());
?>
