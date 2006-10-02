<?php

require("../../lib/simpletest/setup.php");

require("../../conf/Presentation.class.php");

class MyRule extends Rule {
	function valid($value) { return ($value=='ohmy'); }
}

class MyRuleWithDynamicMessage extends Rule {
	function validate($value) { if ($value!='ohmy') return "Value is \"$value\", but should be \"ohmy\""; }
}

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
	
	function testDefaultRule() {
		$r = new Rule('name');
		$this->assertNotNull($r);
		$this->assertTrue($r->valid('something'), "default rule should accept any value except empty");
		$this->assertFalse($r->valid(''), "default rule should reject empty string");
		$this->assertFalse($r->valid(null), "default rule should reject null");
		$this->assertNull($r->validate('s'), "validate(value) should not return anything if valid");
		$this->assertEqual($r->_message, $r->validate(''), "should return error message if value is invalid");
	}
	
	function testCustomRule() {
		$r = new MyRule('name', 'not the default error message');
		$this->assertTrue($r->valid('ohmy'));
		$this->assertFalse($r->valid('oh'));
		$this->assertNull($r->validate('ohmy'));
		$this->assertEqual('not the default error message', $r->validate('oh'));
	}
	
	function testCustomRuleWithMessage() {
		$r = new MyRuleWithDynamicMessage('n');
		$this->assertNull($r->validate('ohmy'));
		$this->assertEqual('Value is "oh", but should be "ohmy"', $r->valid('oh'));
	}
	
	function testRuleEreg() {
		$regexp = '[^@]+@[^@]+';
		$this->sendMessage("The reqular expression in this test is '$regexp'");
		$r = new RuleEreg('name', 'must be a string with @ somewhere in the middle', $regexp);
		$this->assertTrue($r->valid('a@b'));
		$this->assertFalse($r->valid('ab'));
		$this->assertNull($r->validate('a@b'));
		$this->assertNotNull($r->validate('ab'));
	}
	
	function testValidateDirectlyWhenRuleIsCreated() {
		$_REQUEST['myfield'] = '';
		$r = new Rule('myfield');
		$this->sendMessage("If a rule is defined and a matching parameter is given, it should be validated directly.");
		$this->assertError();
		if ($r->valid('')) $this->fail("Seems that the default rule did not validate correctly.");
		unset($_REQUEST['myfield']);
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
	
	function testValidateFieldUsingAJAXRuleFail() {
		// the function does exit() so it is hard to test
		$expect = "{id: 'r_username', value: 'bosse', success: false, msg: 'Your Username is too short (more than 6 characters) Please try again'}";
	}
	
}

testrun(new TestValidation());

?>