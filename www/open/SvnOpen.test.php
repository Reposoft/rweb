<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')
$lastCommand = null;
$nextOutput = array('output');
$nextExitcode = 0;
function _command_run($cmd, $argsString) {
	global $lastCommand, $nextOutput, $nextExitcode;
	$lastCommand = "$cmd $argsString";
	return array_merge($nextOutput, array($nextExitcode));
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
		$_SERVER['PHP_AUTH_USER'] = 'a b';
		$_SERVER['PHP_AUTH_PW'] = 'c"d';
		$this->sendMessage(login_getSvnSwitches());
		$this->assertTrue(strContains(login_getSvnSwitches(), '--username="a b"'), "Username should be escaped for command line.");
		if (isWindows()) {
			$this->assertTrue(strContains(login_getSvnSwitches(), '--password="c""d"'), "Password should be escaped for command line.");
		} else {
			$this->assertTrue(strContains(login_getSvnSwitches(), '--password="c\\"d"'), "Password should be escaped for command line.");
		}
		unset($_SERVER['PHP_AUTH_USER']);
		unset($_SERVER['PHP_AUTH_PW']);
	}
	
	// some things are tested through the SvnEdit class
	
}

testrun(new TestSvnOpen());
?>
