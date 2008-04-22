<?php
require('SvnEdit.class.php');
require('../conf/Presentation.class.php');
require("../lib/simpletest/setup.php");
 
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

function _setNextOutput($arr) {
	global $nextOutput;
	$nextOutput = $arr;
}

class SvnEditTest extends UnitTestCase
{

	function testIsSuccessfulUsingDIRCommand() {
		_setNextExitcode(0);
		$edit = new SvnEdit('info');
		$edit->exec();
		$this->assertTrue($edit->isSuccessful());
	}
	
	function testIsSuccessfulFalse() {
		_setNextExitcode(99);
		$edit = new SvnEdit('info');
		$edit->exec();
		$this->assertEqual(99, $edit->getExitcode(), "test setup error, should use mock value. %s");
		$this->assertFalse($edit->isSuccessful());
	}

	function testExtractRevision() {
		_setNextExitcode(0);
		_setNextOutput(array("Committed revision 107."));
		$edit = new SvnEdit('info');
		$edit->exec();
		$this->assertEqual('107', $edit->getCommittedRevision());
	}
	
	/**
	 * Only some of the command arguments should be escaped, so escaping must be done per argument type.
	 */
	function testAddArgument() {
		$edit = new SvnEdit('info');
		$edit->addArgPath('arg1');
		$edit->addArgOption('arg_');
		$edit->exec();
		$this->assertPattern('/"arg1" arg_/', _getLastCommand());
	}
	
	function testAddArgumentPath() {
		// file and path should only be escaped, not converted with toPath or toShellEncoding
		$edit = new SvnEdit('info');
		$edit->addArgPath('\temp\file.txt');
		$utf8filename = "file\xc3\xa5.txt";
		$edit->addArgFilename($utf8filename);
		$edit->exec();
		$this->assertTrue(strpos(_getLastCommand(), "\"\\\\temp\\\\file.txt\""));
		$utf8command = _getLastCommand();
		$this->sendMessage("Filename '$utf8filename' resulted in command '$utf8command'");
		if (System::isWindows()) {
			// not utf-8, but how to test? nowadays we can use utf-8 on windows too (sometimes)
			//$this->assertFalse(strpos($utf8command, "\"file\xc3\xa5.txt\""));
		} else {
			$this->assertTrue(strpos($utf8command, "\"file\xc3\xa5.txt\""));
		}
	}
	
	function testPercentInFilename(){
		$edit = new SvnEdit('info');
		// internally, urls are not encoded, but they need to be on the command line
		$edit->addArgUrl('http://www.where-we-work.com/%procent%');
		$edit->exec();
		$this->assertTrue(strpos(_getLastCommand(), '"http://www.where-we-work.com/%25procent%25"'));
	}
	
	function testCommand() {
		// actually we shouldnt care much about what the command looks like, but here's one test to help
		$edit = new SvnEdit('info');
		$edit->setMessage('msg');
		$edit->addArgFilename('file.txt');
		$edit->addArgUrl('https://my.repo/file.txt');
		$edit->exec();
		$this->assertPattern('/info "file.txt" "https:\/\/my.repo\/file.txt"/', _getLastCommand());
		$this->assertTrue(strpos(_getLastCommand(),' -m "msg"'));
		$this->assertFalse(strpos(_getLastCommand(),' msg'), "message must be encoded");
	}
	
	function testImportCommand() {
		$edit = new SvnEdit('import');
		$edit->setMessage('msg');
		$edit->addArgPath('a/temp/folder/');
		$edit->addArgUrl('http://www.where-we-work.com/');
		$edit->exec();
		$this->assertFalse(strpos(_getLastCommand(), ' msg'));
	}
	
	function testCommandEscape() {
		if (System::isWindows()) {
			
		} else {
			$edit = new SvnEdit('" $(ls)');
			$edit->setMessage('msg " `ls` \'ls\' \ " | rm END');
			$edit->exec();
			$result = _getLastCommand();
			$this->sendMessage($result);
			//should operation be escaped?//$this->assertTrue(strpos($result, '\" \$\(ls\)'));
			$this->assertTrue(preg_match('/-m\s(.*)END/', $result, $matches));
			$this->assertEqual($matches[1], '"msg \" \`ls\` '."'\\''ls'\\''".' \\\\ \" | rm ');
		}
	}
	
	function testGetOperation() {
		$e = new SvnEdit('diff');
		$this->assertEqual('diff', $e->getOperation());
	}
	
	function testGetArgumentsString() {
		$c = new SvnEdit('commit');
		$c->addArgPath('C:/a/');
		$c->setMessage('m s g');
		//$this->assertEqual('"C:/a/" -m "m s g"', $c->_getArgumentsString());
		//message appended upon exec
		$this->assertEqual('"C:/a/"', $c->_getArgumentsString());
	}
	
	function testNewFilenameRule() {
		$r = new NewFilenameRule('test', '/test/trunk/');
		$this->assertEqual('/test/trunk/a.txt', $r->_getPath('a.txt'));
	}

	function testFilenameRule() {
		$r = new FilenameRule('file');
		$this->assertNull($r->validate('abc.txt'));
		$this->assertEqual('This is a required field', $r->validate(''));
		$this->sendMessage("Message on validate 'a\"': ".$r->validate('a"'));
		$this->assertNotNull($r->validate('a"'), 'double quote not allowed in filename');
		$this->assertNotNull($r->validate('a*'), '* not allowed in filename');
		$this->assertNull($r->validate('a!'), '! is allowed in filename');
		$this->assertNotNull($r->validate("a'"), 'single quoute is not allowed in filename. '
			.'It would be possible but it causes extra work for linux users.');
	}
	
	function testFilenameMaxLength() {
		// this is actually a recommendation but we handle it like a rule
		$r = new FilenameRule('file');
		$this->assertNull($r->validate(str_repeat('a', 50+4)));
		$this->assertNotNull($r->validate(str_repeat('a', 51+4)), "max length 50 + extension .123");
	}
	
	function testFilenameMaxLengthUTF8() {
		// this is actually a recommendation but we handle it like a rule
		$r = new FilenameRule('file');
		$this->assertNull($r->validate(str_repeat("\x81", 50+4)));
		$this->assertNotNull($r->validate(str_repeat("\x81", 51+4)), "max length 50 + extension .123");
	}
	
	function testFilenameRuleSpecialCases() {
		$r = new FilenameRule('file');
		$this->assertNotNull($r->validate('.'));
		$this->assertNotNull($r->validate('..'));
	}
	
	function testFilenameRuleNotRequired() {
		$r = new FilenameRule('file', false);
		$this->assertNull($r->validate(''));
		$this->assertNull($r->validate('abc.txt'));
		$this->sendMessage("Error on invalid characters: ".$r->validate('a\\/'));
		$this->assertNotNull($r->validate('a\\/'));
	}
	
	function testParentFolderExists() {
		$_REQUEST['folder'] = getSelfUrl().'nonexistingresource/';
		$this->expectError(new PatternExpectation('/does not exist/'));
		$r = new ResourceExistsRule('folder');
	}
	
}

testrun(new SvnEditTest());
?>
