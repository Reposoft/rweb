<?php

require("../../lib/simpletest/setup.php");

require("index.php");

// mock 
function setTarget($path) {
	
}

// tests the logic around resolution of project names
class TestProject extends UnitTestCase {
	
	function testProjectFolderInRoot() {
		$path = '/myproject/';
		
	}
	
	function testRootAsProjectFolder() {
		$path = '/';
		
	}
	
	function testProjectFolderInCompay() {
		$path = '/mycompany/myproject/';
		
	}
	
}

testrun(new TestProject());

?>