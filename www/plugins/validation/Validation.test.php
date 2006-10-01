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
		$this->sendMessage('defining a rule for "myfield"');
		rule('myfield', '--');
		$this->assertError();
	}
	
	function testValidate() {
		
	}
	
}

testrun(new TestValidation());

?>