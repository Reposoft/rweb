<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

require("account.inc.php");

// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

class TestAccount extends UnitTestCase {

	function setUp() {
		
	}
	
	function testGetRandomPassword() {
		$iterations = 1000;
		$passwordArray = array();
		for ($i = 0; $i <= $iterations-1; $i++) {
   			$passwordArray[$i] = getRandomPassword('test');
		}
		$this->assertEqual($passwordArray, array_unique($passwordArray));
		$this->assertEqual($iterations, count(array_unique($passwordArray)));
	}
	
}

testrun(new TestAccount());
?>
