<?php

require("../../lib/simpletest/setup.php");

require("../../conf/Presentation.class.php");

class TestValidation extends UnitTestCase {
	
	function testDefineRuleBeforeInput() {
		rule('myfield', '--', false);
		rule('myfield2', '--', true);
	}
	
	function testDefineRuleAfterSubmit() {
		$_REQUEST['myfield'] = 'hello';
		rule('myfield', '--');
		$this->assertError();
	}
	
	function testValidate() {
		
	}
	
}

testrun(new TestValidation());

?>