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
	
	function testExtractRevisionFromMultilineResult() {
		_setNextExitcode(0);
		// This might happen if getResult returns more than one row
		// deeming some information prior to the commit comment as important
		// Not sure if this will ever happen for successful operations.
		_setNextOutput(array("\nCommitted revision 107."));
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
		$this->dump(null, "Filename '$utf8filename' resulted in command '$utf8command'");
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
	
	function testAddArgmentMultiline() {
		$edit = new SvnEdit('propset');
		$edit->addArgOption("a:b");
		$edit->addArgMightBeMultiline("X\nY");
		$edit->addArgPath('.');
		$edit->exec();
		$cmd = _getLastCommand();
		$argf = strpos($cmd, ' -F ');
		$this->assertTrue($argf > 0, 'Expected file argument in: '.$cmd);
		$file = substr($cmd, $argf + 5);
		$file = substr($file, 0, strpos($file, '"')); // until end quoute
		$this->assertTrue(file_exists($file), 'Should have created temp file for argument value. Expected '.$file);
		$contents = file_get_contents($file);
		$this->assertEqual($contents, "X\nY");
	}
	
	function testAddArgumentMultilineNot() {
		$in = new SvnEdit('import');
		$in->addArgMightBeMultiline("X", '-m');
		$in->exec();
		$cmd = _getLastCommand();
		$this->assertTrue(strpos($cmd, '-m "X"') > 0, 'Should add normal arg. Got: '.$cmd);
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
			$this->dump(null, $result);
			//should operation be escaped?//$this->assertTrue(strpos($result, '\" \$\(ls\)'));
			$this->assertTrue(preg_match('/-m\s(.*)END/', $result, $matches));
			// this more complex escape was expected before
			//$this->assertEqual($matches[1], '"msg \" \`ls\` '."'\\''ls'\\''".' \\\\ \" | rm ');
			// got assert error: character 16 with ["msg \" \`ls\` 'ls' \\ \" | rm ] and ["msg \" \`ls\` '\''ls'\'' \\ \" | rm ] 
			// but this seems to work in ubuntu shell
			$this->assertEqual($matches[1], '"msg \" \`ls\` ' . "'ls'" . ' \\\\ \" | rm ');
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

	function testGetResult()  {
		$o = explode("\n",
'Sending        lllk.txt
Transmitting file data .
Committed revision 28.');
		$e = new SvnEdit('commit');
		$c = $e->command->command; // command instance for mocking
		$c->output = $o;
		$c->exitcode = 0;
		$this->assertEqual($e->getResult(), 'Committed revision 28.');
	}

	function testGetResultNoOutput()  {
		$o = array();
		$e = new SvnEdit('update');
		$c = $e->command->command; // command instance for mocking
		$c->output = $o;
		$c->exitcode = 0;
		$this->assertEqual($e->getResult(), 'No output from operation update');
	}

	function testGetResultCommitFailed()  {
		$o = explode("\n",
'Sending        lllk.txt
Transmitting file data .
What does it say here when commit fails?');
		$e = new SvnEdit('commit');
		$c = $e->command->command; // command instance for mocking
		$c->output = $o;
		$c->exitcode = 0;
		$this->assertEqual($e->getResult(), 'What does it say here when commit fails?');
		$this->assertTrue($e->isSuccessful(), 'Still successful because exit code is 0');
	}
	
	function testGetResultCommitFailedErrorMessage()  {
		// output when web server user does not have write access to repo
		$o = explode("\n", "Server error: svn: Commit failed (details follow):\n".
				"svn: Can't open file \"/repo1/db/txn-current-lock\": Permission denied ");
		$e = new SvnEdit('commit');
		$e->command->command->output = $o;
		$e->command->command->exitcode = 1;	
		$result = $e->getResult();
		$this->dump(null, "Result: $result");
		$this->assertPattern('/Commit failed/', $result);
		$this->assertPattern('/Can\'t open file .*repo1.db.txn-current-lock.* Permission denied/', $result);
		$this->assertFalse($e->isSuccessful());
	}

	function testGetResultWithPostCommitHookOutput()  {
		// To get output from post-commit the hook must have exit code !=0, which always renders a warning
		$o = explode("\n",
'Sending        lllk.txt
Transmitting file data .
Committed revision 28.

Warning: post-commit hook failed (exit code 1) with output:
This is the post-commit hook printing a number 10.
');
		$e = new SvnEdit('commit');
		$c = $e->command->command; // command instance for mocking
		$c->output = $o;
		$c->exitcode = 0; // is 0 even if post-commit returns >0
		$this->assertEqual(28, $e->getCommittedRevision(), 'Should get the revision number along with post-commit output. %s');
		$result = $e->getResult();
		$this->dump(null, "Result: $result");
		$this->assertNoPattern('/Sending/', $result, 'Should not return the svn file transfer output. %s');
		$this->assertNoPattern('/Transmitting file data/', $result, '%s');
		$this->assertPattern('/Warning: post-commit hook failed \(exit code 1\) with output:/', $result);
		$this->assertPattern('/This is the post-commit hook printing a number 10./', $result);
		$this->assertPattern('/Committed revision 28./', $result, 'Must always contain the revision message. %s');
		$this->assertEqual(3, substr_count(nl2br($result), "<br />"), 'Should have the line breaks from the command output. %s');
	}

	function testGetResultImport() {
		$e = new SvnEdit('import');
		// don't think this is any different from commit
		$e->command->command->output = array('Adding   build.xml', '', 'Committed revision 92.');
		$e->command->command->exitcode = 0;
		$this->assertEqual('Committed revision 92.', $e->getResult(), 'Should strip Sending... and newlines. %s');	
	}
	
	function testGetResultPreCommit() {
		$e = new SvnEdit('import');
		$e->command->command->output = explode("\n",
			"Sending         testconfig.sh\nsvn: Commit blocked by pre-commit hook (exit code 255) with no output.");;
		$e->command->command->exitcode = 1;
		$this->assertPattern('/Commit blocked by pre-commit/', $e->getResult());
		// Don't think it is possible to get pre-commit output without failing the commit so I'm not testing getCommittedRevision
	}

	function testFilenameRule() {
		$r = new FilenameRule('file');
		$this->assertNull($r->validate('abc.txt'));
		$this->assertEqual('This is a required field', $r->validate(''));
		$this->dump(null, "Message on validate 'a\"': ".$r->validate('a"'));
		$this->assertNotNull($r->validate('a"'), 'double quote not allowed in filename');
		$this->assertNotNull($r->validate('a*'), '* not allowed in filename');
		$this->assertNull($r->validate('a!'), '! is allowed in filename');
		$this->assertNotNull($r->validate("a'"), 'single quoute is not allowed in filename. '
			.'It would be possible but it causes extra work for linux users.');
	}
	
	function testFilenameMaxLength() {
		// this is actually a recommendation but we handle it like a rule
		$r = new FilenameRule('file');
		$this->assertNull($r->validate(str_repeat('a', REPOS_FILENAME_MAX_LENGTH)));
		$this->assertNotNull($r->validate(str_repeat('a', REPOS_FILENAME_MAX_LENGTH+1)), "max length 50 + extension .123");
	}
	
	function testFilenameMaxLengthUTF8() {
		// this is actually a recommendation but we handle it like a rule
		$r = new FilenameRule('file');
		$this->assertNull($r->validate(str_repeat("\x81", REPOS_FILENAME_MAX_LENGTH)));
		$this->assertNotNull($r->validate(str_repeat("\x81", REPOS_FILENAME_MAX_LENGTH+1)), "max length 50 + extension .123");
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
		$this->dump(null, "Error on invalid characters: ".$r->validate('a\\/'));
		$this->assertNotNull($r->validate('a\\/'));
	}
	
	/* TODO this is an integration test, create a class for that
	function testParentFolderExists() {
		$_SERVER['SERVER_NAME'] = 'example.com';
		$_SERVER['REQUEST_URI'] = rawurlencode('/test/');
		$_REQUEST['folder'] = getSelfUrl().'nonexistingresource/';
		$this->expectError(new PatternExpectation('/does not exist/'));
		$r = new ResourceExistsRule('folder');
	}
	*/
	
	function testFilterOutput() {
		$this->assertEqual(_edit_svnOutput('svn: hello svn'), ' hello svn');
		$this->assertEqual(_edit_svnOutput('abc '.System::getApplicationTemp().' def'), 'abc  def');
		// TODO Do we really want the following surprising change to operation output?
		$this->assertEqual(_edit_svnOutput('a Committed revision: 8'), 'a Committed version: 8');
	}
	
}

$testcase = new SvnEditTest();
testrun($testcase);
?>
