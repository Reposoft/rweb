<?php
require("../../open/ServiceRequest.class.php");
require("validation.inc.php");
require("../../lib/simpletest/setup.php");

class MyRule extends Rule {
	function valid($value) { return ($value=='ohmy'); }
}

class MyRuleWithDynamicMessage extends Rule {
	function validate($value) { if ($value!='ohmy') return "Value is \"$value\", but should be \"ohmy\""; }
}

class MyRuleRegexp extends RuleRegexp {
	function MyRuleRegexp($fieldname) {
		$this->RuleRegexp($fieldname,	'empty or 1-5 lowercase letters please', '/^[a-z]{0,5}$/');
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
	
	function testRuleRegexp() {
		$regexp = '/[^@]+@[^@]+/';
		$this->sendMessage("The reqular expression in this test is '$regexp'");
		$r = new RuleRegexp('name', 'must be a string with @ somewhere in the middle', $regexp);
		$this->assertTrue($r->valid('a@b'));
		$this->assertFalse($r->valid('ab'));
		$this->assertNull($r->validate('a@b'));
		$this->assertNotNull($r->validate('ab'));
	}
	
	function testExtendRuleRegexp() {
		$r = new MyRuleRegexp('a');
		$this->assertEqual('a', $r->fieldname);
		$this->assertFalse(empty($r->_message));
		$this->assertFalse(empty($r->regexp));
		$this->assertTrue($r->valid('abc'));
		$this->assertTrue($r->valid(''), "Empty string should be allowed by the regex $r->regexp");
		$this->assertNull($r->validate(''));
		$this->assertNotNull($r->validate('123456789'));	
	}
	
	function testValidateDirectlyWhenRuleIsCreated() {
		$_REQUEST['myfield'] = '';
		$this->expectError(new PatternExpectation('/Error.*myfield/'), 'The "myfield" parameter is set, so when the validation rule is created it should validate directly. %s');
		$r = new Rule('myfield');
		$this->sendMessage("If a rule is defined and a matching parameter is given, it should be validated directly.");
		// verify that validation rule works: if ($r->valid('')) $this->fail("Test error. The default rule does not validate correctly.");
		unset($_REQUEST['myfield']);
	}
	
	function testExpectFields() {
		$_REQUEST['myfield'] = '';
		Validation::expect('myfield');
		$this->assertNoErrors("Expected fields don't need a value, they just need to be defined.");
		
		// now expect two fields, second one missing
		$this->expectError(new PatternExpectation('/formfield2/'),'No "formfield2" is submitted, so we should get an error'); 
		Validation::expect('myfield', 'formfield2');
		unset($_REQUEST['myfield']);
	}
	
	function testValidateFieldUsingAJAX() {
		$url = dirname($_SERVER['SCRIPT_URI']).'/';
		$params = array(VALIDATION_KEY=>'','name'=>'somename');
		
		$s = new ServiceRequest($url, $params, false);
		$this->sendMessage("Request url: ".$s->_buildUrl());
		$s->exec();
		$this->assertEqual(200, $s->getStatus(), "Should have got a response. %s");
		$result = $s->getResponse();
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
		
		$url = dirname($_SERVER['SCRIPT_URI']).'/';
		$params = array(VALIDATION_KEY=>'', 'testuser'=>'tes');
		$s = new ServiceRequest($url, $params, false);
		$s->exec();
		$result = $s->getResponse();
		$this->assertEqual($expected, $json->decode($result));
	}
	
	function testValidateGetValue() {
		$rule = new MyRule('name');
		$this->assertEqual(null, $rule->getValue(), 'Value should be null before field is validated. %s');
		$rule->validate('ohmy');
		$this->assertEqual('ohmy', $rule->getValue(), 'Value should be set when validation has completed. %s');
	}
	
}

testrun(new TestValidation());

?>