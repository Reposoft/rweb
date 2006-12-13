<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require("Command.class.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

$lastCommand = null;
function _command_run($cmd, $argsString) {
	global $lastCommand;
	$lastCommand = "$cmd $argsString";
}

class TestCommand extends UnitTestCase {

	function setUp() {
		
	}

	function testEscapeArgument() {
		// common escape rules
		$this->assertEqual("\"a\\\\b\"", Command::_escapeArgument('a\b'));
		// rules that depend on OS
		if (!isWindows()) {
			$this->assertEqual('"a\"b"', Command::_escapeArgument('a"b'));	
		}
		if (isWindows()) {
			$this->assertEqual('"a""b"', Command::_escapeArgument('a"b'));	
		}
	}
	
	function testEscapeWindowsEnv() {
		$this->assertEqual('"a%b"', Command::_escapeArgument('a%b'), 'single percent should not be a problem');
		$this->assertEqual('"a%NOT-AN-ENV-ENTRY%b"', Command::_escapeArgument('a%NOT-AN-ENV-ENTRY%b'),
			'double percent enclosing something that has not been SET should not be a problem');
		if (isWindows()) {
			$this->assertTrue(getenv('OS'), 'For this test to work OS must be an environment variable');
			$this->assertEqual('"a%OS#b"', Command::_escapeArgument('a%OS%b'));
			$this->assertEqual('"%OS#b%"', Command::_escapeArgument('%OS%b%'));
			$this->assertEqual('"%O%OS#b%"', Command::_escapeArgument('%O%OS%b%'));
			$this->assertEqual('"%OS#OS%"', Command::_escapeArgument('%OS%OS%'));
			$this->assertEqual('"%OS#b%%cd%OS#"', Command::_escapeArgument('%OS%b%%cd%OS%'));
		}
	}
	
	function testGetScriptWrapper() {
		$this->sendMessage("only relevant on *nix");
		if (!isWindows()) {
			$this->assertTrue(file_exists(_repos_getScriptWrapper()), "the script wrapper file "._repos_getScriptWrapper()." does not exist");
		}
	}
	
	function testExclamationMarkInPrompt() {
		// in interactive mode in bash, exclamation marks must be escaped
		// but \ does not work (it is stored with the log message) so the best option is probably "$'\x21'"
		// but, hopefully we don't run in interactive mode
		$out = null;
		$return = null;
		$v = exec('echo test!ing', $out, $return);
		$this->assertEqual(0, $return);
		$this->assertEqual('test!ing', $v);
	}
	
	function testCommandLineEncoding() {
		// plain ascii
		$v = exec("echo nada");
		$this->assertEqual("nada", $v);
		if (isWindows()) {
			// Test latin-1.
			// seems like the echo command is not that great in windows //$v = exec("echo n\xE4d\xE5");
			//$this->assertEqual('ISO-8859-1', mb_detect_encoding($v, 'UTF-8, ISO-8859-1'));
			//$this->assertEqual("n\xE4d\xE5", $v, "Expecting windows server to use Latin-1 encoding. %s");
			//$this->assertEqual("6e e4 64 e5 ", $this->getchars($v));
			
			$this->assertFalse(file_exists(mb_convert_encoding("n\xc3\xa4d\xc3\xa5", 'ISO-8859-1', 'UTF-8')));
			exec("mkdir n\xE4d\xE5"); // instead of testing with 'echo'
			$this->assertTrue(file_exists(mb_convert_encoding("n\xc3\xa4d\xc3\xa5", 'ISO-8859-1', 'UTF-8')),
				"Expecting windows server to use Latin-1 encoding. %s");
			exec("rmdir /q n\xE4d\xE5"); // seems asyncronous, so the assertfalse will be done next time test is run
		} else {
			// utf-8
			$v = exec("echo n\xc3\xa4d\xc3\xa5");
			$this->assertEqual("n\xc3\xa4d\xc3\xa5", $v);
			$this->assertEqual("6e c3 a4 64 c3 a5 ", $this->getchars($v));
		}
	}
	
	function getchars($string) {
		$c = "";
		for ($i=0;$i<strlen($string);$i++) {
	   	$chr = $string{$i};
	   	$ord = ord($chr);
	   	$c .= dechex($ord)." ";
		}
		return $c;
	}	
	
}

testrun(new TestCommand());
?>
