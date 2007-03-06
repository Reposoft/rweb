<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require("PresentEdit.class.php");
// need to test the cooperation with view result page
//require("../view/index.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

class TestPresentEdit extends UnitTestCase {

	function setUp() {
		
	}
	
	function testPresentEditStart() {
		
	}
	
}

testrun(new TestPresentEdit());
?>
