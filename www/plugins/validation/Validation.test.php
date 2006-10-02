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
	
	function testValidateFieldUsingAJAX() {
		$url = repos_getSelfUrl();
		$this->sendMessage("This test has URL $url, file is ".basename(__FILE__));
		if (!strEnds($url, basename(__FILE__))) $this->fail("Can not get URL of this test, aborting AJAX test.");
		$url = getParent($url).'?validate=1&name=somename';
		$this->sendMessage("Request url: $url");
		$handle = fopen($url, 'r');
		$result = fread($handle, 32*1024); // max response size for test
		fclose($handle);
		$this->assertFalse(strContains($result, '<html'), "Should not return HTML, only a response string.");
		$this->sendMessage(array('Result:',$result));
		
	}
	
}

testrun(new TestValidation());

?>