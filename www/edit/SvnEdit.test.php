<?php
require('SvnEdit.class.php');
require("../lib/simpletest/setup.php");
 
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
		$edit->addArgFilename("file\xc3\xa5.txt");
		$edit->exec();
		$this->assertTrue(strpos(_getLastCommand(), "\"\\\\temp\\\\file.txt\""));
		$this->assertTrue(strpos(_getLastCommand(), "\"file\xc3\xa5.txt\""));
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
		if (substr(PHP_OS, 0, 3) != 'WIN') {
			$edit = new SvnEdit('" $(ls)');
			$edit->setMessage('msg " `ls` \'ls\' \ " | rm');
			$edit->exec();
			$this->assertTrue(strpos(_getLastCommand(), '\" \$\(ls\) -m "msg \" \`ls\` \'ls\' \\\\ \" | rm"'));
		}
	}
	
	function testNewFilenameRule() {
		$r = new NewFilenameRule('test', '/test/trunk/');
		$this->assertEqual('/test/trunk/a.txt', $r->_getPath('a.txt'));
	}
	
	function testGetResourceTypeNonExisting() {
		$url = '/demoproject/trunk/public/does-not-exist-adsferqwerw/';
		$this->assertEqual(0, login_getResourceType($url));
		$this->assertTrue(login_getResourceType($url)==false);
		$this->assertFalse(login_getResourceType($url)===false);
	}

	// ---- tests below are for the homeless getResourceType ----
	
	function testGetResourceTypeFolder() {
		$url = '/demoproject/trunk/public/';
		$this->assertEqual(1, login_getResourceType($url));
		$this->assertTrue(login_getResourceType($url)==true); // it does exist
		$this->assertFalse(login_getResourceType($url)===true); // never returns boolean true
	}

	function testGetResourceTypeFolderNoSlash() {
		$url = '/demoproject/trunk/public'; // must be able to accept folders without tailing slash (for svn log --xml output)
		$this->assertEqual(1, login_getResourceType($url));
		$this->assertTrue(login_getResourceType($url)==true); // it does exist
		$this->assertFalse(login_getResourceType($url)===true); // never returns boolean true
	}
	
	function testGetResourceTypeFolderWithTestUser() {
		if (isLoggedIn() && getReposUser()=='test') {
		$url = '/test/trunk';
		$this->assertEqual(1, login_getResourceType($url));
		} else {
			$this->sendMessage("test user is not logged in, so this test is skipped");
		}
	}

}

testrun(new SvnEditTest());
?>