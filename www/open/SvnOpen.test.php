<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')
$lastCommand = null;
$nextOutput = array('output');
$nextExitcode = 0;
function _command_run($cmd, &$result) {
	global $lastCommand, $nextOutput, $nextExitcode;
	$lastCommand = "$cmd";
	$result = $nextOutput;
	return $nextExitcode;
}
function _getLastCommand() {
	global $lastCommand;
	return $lastCommand;
}
function _setNextExitcode($int) {
	global $nextExitcode;
	$nextExitcode = $int;
}
// import the script under test
require("SvnOpen.class.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

class TestSvnOpen extends UnitTestCase {

	function testGetSvnSwitches() {
		setTestUser('a b', 'c"d');
		$this->sendMessage(_svnopen_getSvnSwitches());
		$this->assertTrue(strContains(_svnopen_getSvnSwitches(), '--username="a b"'), "Username should be escaped for command line.");
		if (System::isWindows()) {
			$this->assertTrue(strContains(_svnopen_getSvnSwitches(), '--password="c""d"'), "Password should be escaped for command line.");
		} else {
			$this->assertTrue(strContains(_svnopen_getSvnSwitches(), '--password="c\\"d"'), "Password should be escaped for command line.");
		}
		setTestUserNotLoggedIn();
	}
	
	function testRevisionRuleNumeric() {
		$r = new RevisionRule();		
		$this->assertEqual('', $r->validate("12"));
		$this->assertEqual('', $r->validate("0"));
		$this->assertEqual('', $r->validate("HEAD"));
		$this->assertEqual('', $r->validate("{2006-01-10}"));
		$this->assertPattern('/[Nn]ot.*valid/', $r->validate("-1"));
		$this->assertPattern('/[Nn]ot.*valid/', $r->validate("BASE"));
		$this->assertPattern('/[Nn]ot.*valid/', $r->validate("{2006-12-10"));
	}
	
	function testRevisionRuleAuto() {
		$_REQUEST['rev'] = "12";
		$r = new RevisionRule();
		$this->assertEqual(12, $r->getValue());
		
		$_REQUEST['rev'] = "-1";
		$this->expectError();
		$r = new RevisionRule();
		$this->assertNull($r->getValue());
		
		unset($_REQUEST['rev']);
	}
	
	function testRevisionRuleCustomField() {
		$_REQUEST['fromrev'] = "12";
		$r = new RevisionRule('fromrev', 'my error');
		$this->assertEqual(12, $r->getValue());
		
		unset($_REQUEST['fromrev']);
	}
	
	function testGetOperation() {
		$o = new SvnOpen('diff');
		$this->assertEqual('diff', $o->getOperation());
	}
	
	function testGetArgumentsString() {
		$o = new SvnOpen('ls');
		$o->addArgOption('-r', '1:2', false);
		$this->assertEqual('-r 1:2', $o->_getArgumentsString());
		$o->addArgPath('/a/b ls c/');
		$this->assertEqual('-r 1:2 "/a/b ls c/"', $o->_getArgumentsString());
	}
	
	function testDetectAuthenticationFailure() {
		$output = array();
		$output[0] = "svn: CHECKOUT of '/data/!svn/ver/20/trunk': authorization failed (http://localhost)";
		
		
	}
	
}

testrun(new TestSvnOpen());
?>
