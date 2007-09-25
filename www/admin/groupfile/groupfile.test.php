<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require("index.php");

// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../../lib/simpletest/setup.php");

class TestGroupfile extends UnitTestCase {

	function setUp() {
		
	}
	
	function testExtract1() {
		$acl = preg_split('/\r?\n/','
[groups]
administrators = admin
demoproject = svensson, test, Sv@n s-on, admin

[/]
@administrators = rw
');
		$groups = groupfileExtract($acl);
		$this->assertEqual(2, count($groups), "Should contain two groups. %s");
		$this->assertEqual('administrators: admin', $groups[0]);
		$this->assertEqual('demoproject: svensson test "Sv@n s-on" admin', $groups[1]);
	}
	
}

testrun(new TestGroupfile());
?>
