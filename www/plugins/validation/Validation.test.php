<?php

require("../../lib/simpletest/setup.php");

require("../../conf/Presentation.class.php");

class TestValidation extends UnitTestCase {
	
	function testJson() {
		// create a new instance of Services_JSON
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		
		$value = array('id'=>'name', 'value'=>'svensson', 'success'=>true); // array(1, 2, 'baz')));
		$output = $json->encode($value);
		
		$this->assertEqual('{"id":"name","value":"svensson","success":true}', $output);
		
		$backagain = $json->decode($output);
		$this->assertEqual($value, $backagain);
	}
	
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
		//$handle = fsockopen($url, 80, $errno, $errstr, 5); // seems to require special php config
		$handle = fopen($url, 'r');
		//if (!$handle) $this->fail("Could not open connection to validator. Error $errno: $errstr");
		$result = fgets($handle);
		$this->assertTrue(feof($handle), "response should be only one line");
		fclose($handle);
		$this->assertTrue(strlen($result)>0, "Should have got a response.");
		$this->assertFalse(strContains($result, '<html'), "Should not return HTML, only a response string.");
		$this->sendMessage(array('Result:',$result));
		
		// should be valid
		$this->assertEqual("{id: 'name', value: 'somename', success: true, msg: ''}", $result);
	}
	
	function testValidateFieldRuleFail() {
		$expect = "{id: 'r_username', value: 'bosse', success: false, msg: 'Your Username is too short (more than 6 characters) Please try again'}";
	}
	
}

testrun(new TestValidation());

?>