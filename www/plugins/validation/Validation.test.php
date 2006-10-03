<?php

require("../../lib/simpletest/setup.php");

require("../../conf/Presentation.class.php");

class MyRule extends Rule {
	function valid($value) { return ($value=='ohmy'); }
}

class MyRuleWithDynamicMessage extends Rule {
	function validate($value) { if ($value!='ohmy') return "Value is \"$value\", but should be \"ohmy\""; }
}

class MyRuleEreg extends RuleEreg {
	function MyRuleEreg ($fieldname) {
		$this->RuleEreg($fieldname,	'empty or 1-5 lowercase letters please', '^[a-z]{0,5}$');
	}
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
	
	function testExtendRuleEreg() {
		$r = new MyRuleEreg('a');
		$this->assertEqual('a', $r->fieldname);
		$this->assertFalse(empty($r->_message));
		$this->assertFalse(empty($r->regex));
		$this->assertTrue($r->valid('abc'));
		$this->assertTrue($r->valid(''), "Empty string should be allowed by the regex $r->regex");
		$this->assertNull($r->validate(''));
		$this->assertNotNull($r->validate('123456789'));	
	}
	
	function testValidateDirectlyWhenRuleIsCreated() {
		$_REQUEST['myfield'] = '';
		$r = new Rule('myfield');
		$this->sendMessage("If a rule is defined and a matching parameter is given, it should be validated directly.");
		$this->assertError();
		if ($r->valid('')) $this->fail("Seems that the default rule did not validate correctly.");
		unset($_REQUEST['myfield']);
	}
	
	function testExpectedFields() {
		$_REQUEST['myfield'] = '';
		Validation::expect('myfield');
		$this->assertNoErrors("Expected fields don't need a value, they just need to be defined.");
		Validation::expect('myfield', 'formfield2');
		$this->assertErrorPattern('/formfield2/');
		unset($_REQUEST['myfield']);
	}
	
	function testValidateFieldUsingAJAX() {
		$url = repos_getSelfUrl();
		$this->sendMessage("This test has URL $url, file is ".basename(__FILE__));
		if (!strEnds($url, basename(__FILE__))) $this->fail("Can not get URL of this test, aborting AJAX test.");
		$url = getParent($url).'?validation&name=somename';
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
		$expected = array('id'=>'name', 'value'=>'somename', 'success'=>true, 'msg'=>'');
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$this->assertEqual($expected, $json->decode($result));
	}
	
	function testValidateFieldUsingAJAXRuleFail() {
		$errormsg = 'Username is 4-20 characters and can not contain special characters'; // from the test page
		$expect = "{id: 'testuser', value: 'tes', success: false, msg: '$errormsg'}";
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$expected = $json->decode($expect);
		
		$url = getParent(repos_getSelfUrl()).'?validation&testuser=tes';
		$handle = fopen($url, 'r');
		$result = fgets($handle);
		fclose($handle);
		
		$this->assertEqual($expected, $json->decode($result));
	}
	
}

testrun(new TestValidation());

?>