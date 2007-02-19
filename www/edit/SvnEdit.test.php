<?php
require('SvnEdit.class.php');
require('../conf/Presentation.class.php');
require("../lib/simpletest/setup.php");
 
$lastCommand = null;
$nextOutput = array('output');
$nextExitcode = 0;
function _command_run($cmd) {
	global $lastCommand, $nextOutput, $nextExitcode;
	$lastCommand = "$cmd";
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
		$utf8filename = "file\xc3\xa5.txt";
		$edit->addArgFilename($utf8filename);
		$edit->exec();
		$this->assertTrue(strpos(_getLastCommand(), "\"\\\\temp\\\\file.txt\""));
		$utf8command = _getLastCommand();
		$this->sendMessage("Filename '$utf8filename' resulted in command '$utf8command'");
		if (System::isWindows()) {
			// not utf-8, but how to test?
			$this->assertFalse(strpos($utf8command, "\"file\xc3\xa5.txt\""));
		} else {
			$this->assertFalse(strpos($utf8command, "\"file\xc3\xa5.txt\""));
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
		if (substr(PHP_OS, 0, 3) != 'WIN') {
			$edit = new SvnEdit('" $(ls)');
			$edit->setMessage('msg " `ls` \'ls\' \ " | rm');
			$edit->exec();
			$this->assertTrue(strpos(_getLastCommand(), '\" \$\(ls\) -m "msg \" \`ls\` \'ls\' \\\\ \" | rm"'));
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
	
	function testIsHttpHeadersForFolder() {
		$h = '
		|HTTP/1.1 200 OK|
		|Date: Wed, 04 Oct 2006 19:20:44 GMT|
		|Server: Apache/2.0.59 (Win32) SVN/1.4.0 PHP/5.1.6 DAV/2|
		|Last-Modified: Wed, 04 Oct 2006 19:17:23 GMT|
		|ETag: W/"1//demoproject/trunk/public"|
		|Accept-Ranges: bytes|
		|Connection: close|
		|Content-Type: text/xml|
		'; // copied from the headers test
		foreach(explode('|', $h) as $row) {
			if (strContains($row, 'HTTP/1.')) $headers = array($row);
			if (strContains($row, ':')) { $r = explode(':', $row); $headers[$r[0]] = trim($r[1]); }
		}
		$this->assertTrue(_isHttpHeadersForFolder($headers), "folder headers: $h");
	}
		
	function testIsHttpHeadersForFolderFile() {
		$h = '
		|HTTP/1.1 200 OK|
		|Date: Thu, 05 Oct 2006 08:55:52 GMT|
		|Server: Apache/2.0.59 (Win32) SVN/1.4.0 PHP/5.1.6 DAV/2|
		|Last-Modified: Thu, 05 Oct 2006 07:37:59 GMT|
		|ETag: "1//demoproject/trunk/public/xmlfile.xml"|
		|Accept-Ranges: bytes|
		|Content-Length: 17|
		|Connection: close|
		|Content-Type: text/xml|
		'; // copied from the headers test
		foreach(explode('|', $h) as $row) {
			if (strContains($row, 'HTTP/1.')) $headers = array($row);
			if (strContains($row, ':')) { $r = explode(':', $row); $headers[$r[0]] = trim($r[1]); }
		}
		$this->assertFalse(_isHttpHeadersForFolder($headers), "file headers: $h");
	}

	function testFilenameRule() {
		$r = new FilenameRule('file');
		$this->assertNull($r->validate('abc.txt'));
		$this->assertEqual('This is a required field', $r->validate(''));
		$this->assertNull($r->validate(str_repeat('a', 50)));
		$this->assertNotNull($r->validate(str_repeat('a', 51)), "max length 50");
		$this->sendMessage("Message on validate 'a\"': ".$r->validate('a"'));
		$this->assertNotNull($r->validate('a"'), 'double quote not allowed in filename');
		$this->assertNotNull($r->validate('a*'), '* not allowed in filename');
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
	
}

testrun(new SvnEditTest());
?>