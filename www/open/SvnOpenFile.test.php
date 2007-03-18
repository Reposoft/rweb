<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require("SvnOpenFile.class.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

// it is _not_ possible to figure out if a file is readonly based on svn info (as far as i know). all commit info is there anyway.

class TestSvnOpenFile extends UnitTestCase {

	function setUp() {
		_svnOpenFile_setInstance(null);
	}
	
	function testParseListXml() {
		$list = explode("\n",
			'<?xml version="1.0"?>
			<lists>
			<list
			   path="http://localhost/testrepo/test/a.txt">
			<entry
			   kind="file">
			<name>a.txt</name>
			<size>12345678</size>
			<commit
			   revision="2">
			<author>SYSTEM</author>
			<date>2007-01-10T18:38:13.679203Z</date>
			</commit>
			</entry>
			</list>
			</lists>');
		$file = new SvnOpenFile('/test/a.txt');
		$a = $file->_parseListXml($list);
		$this->assertEqual('file', $a['kind']);
		$this->assertEqual('a.txt', $a['name']);
		$this->assertEqual('12345678', $a['size']);
		$this->assertEqual('2', $a['revision']);
		$this->assertEqual('SYSTEM', $a['author']);
		$this->assertEqual('2007-01-10T18:38:13.679203Z', $a['date']);
	}
	
	function testParseListXmlLock() {
		$list = explode("\n",
			'<?xml version="1.0"?>
			<lists>
			<list
			   path="http://localhost/testrepo/demoproject/trunk/public/locked-file.txt">
			<entry
			   kind="file">
			<name>locked-file.txt</name>
			<size>56</size>
			<commit
			   revision="2">
			<author>SYSTEM</author>
			<date>2007-01-10T18:38:13.679203Z</date>
			</commit>
			<lock>
			<token>opaquelocktoken:93061e3e-98df-404d-9380-2f821a73bfc9</token>
			<owner>test</owner>
			<comment>Me work</comment>
			<created>2007-01-11T07:33:55.350755Z</created>
			</lock>
			</entry>
			</list>
			</lists>');
		$file = new SvnOpenFile('/demoproject/trunk/public/locked-file.txt');
		$a = $file->_parseListXml($list);
		$this->assertEqual('file', $a['kind']);
		$this->assertEqual('locked-file.txt', $a['name']);
		$this->assertEqual('56', $a['size']);
		$this->assertEqual('2', $a['revision']);
		$this->assertEqual('SYSTEM', $a['author']);
		$this->assertEqual('2007-01-10T18:38:13.679203Z', $a['date']);
		$this->assertEqual('opaquelocktoken:93061e3e-98df-404d-9380-2f821a73bfc9', $a['locktoken']);
		$this->assertEqual('test', $a['lockowner']);
		$this->assertEqual('Me work', $a['lockcomment']);
		$this->assertEqual('2007-01-11T07:33:55.350755Z', $a['lockcreated']);
	}

	function testParseListXmlLockNoMessage() {
		$list = explode("\n",
			'<?xml version="1.0"?>
			<lists>
			<list
			   path="http://localhost/testrepo/demoproject/trunk/public/locked-file.txt">
			<entry
			   kind="file">
			<name>locked-file.txt</name>
			<size>56</size>
			<commit
			   revision="2">
			<author>SYSTEM</author>
			<date>2007-01-10T18:38:13.679203Z</date>
			</commit>
			<lock>
			<token>opaquelocktoken:93061e3e-98df-404d-9380-2f821a73bfc9</token>
			<owner>test</owner>
			<created>2007-01-11T07:33:55.350755Z</created>
			</lock>
			</entry>
			</list>
			</lists>');
		$file = new SvnOpenFile('/demoproject/trunk/public/locked-file.txt');
		$a = $file->_parseListXml($list);
		$this->assertEqual('opaquelocktoken:93061e3e-98df-404d-9380-2f821a73bfc9', $a['locktoken']);
		$this->assertEqual('test', $a['lockowner']);
		$this->assertEqual('2007-01-11T07:33:55.350755Z', $a['lockcreated']);
		$this->assertFalse(isset($a['lockcomment']));
		// see that it translates to empty message
		$file->file = $a;
		$this->assertEqual('', $file->getLockComment());
	}
	
	function testGetRevisionNumberFromETag() {
		$headers = array(
		0 => 'HTTP/1.1 200 OK',
		'Date' => 'Thu, 11 Jan 2007 08:04:19 GMT',
		'Server' => 'Apache/2.0.59 (Win32) DAV/2 mod_ssl/2.0.59 OpenSSL/0.9.8d SVN/1.4.2 PHP/4.3.11',
		'Last-Modified' => 'Wed, 10 Jan 2007 18:38:11 GMT',
		'ETag' => '"54321//demoproject/trunk/public/xmlfile.xml"',
		'Accept-Ranges' => 'bytes',
		'Content-Length' => '18',
		'Content-Type' => 'text/xml'
		);
		$file = new SvnOpenFile('/test/a.txt');
		$file->head = $headers;
		$file->headStatus = 200;
		$this->assertEqual(54321, $file->_getHeadRevisionFromETag());
	}
	
	function testIsLatestRevision_HEAD() {
		$file = new SvnOpenFile('/test/a.txt');
		$this->assertTrue($file->isLatestRevision());
	}
	
	function testIsLatestRevision_etag() {
		$file = new SvnOpenFile('/test/a.txt', 54321);
		$file->head = array(0 => 'HTTP/1.1 200 OK',
			'ETag' => '"54321//demoproject/trunk/public/xmlfile.xml"');
		$file->headStatus = 200;
		$this->assertTrue($file->isLatestRevision());
	}
	
	function testIsLatestRevision_newetag() {
		$file = new SvnOpenFile('/test/a.txt', 54321);
		$file->head = array(0 => 'HTTP/1.1 200 OK',
			'ETag' => '"54322//demoproject/trunk/public/xmlfile.xml"');
		$file->headStatus = 200;
		$this->assertFalse($file->isLatestRevision());
	}

	function testGetTypeFromHeader() {
		$file = new SvnOpenFile('/test/a.eps');
		$file->head = array(0 => 'HTTP/1.1 200 OK',
			'Content-Type' => 'application/octet-stream');
		$file->headStatus = 200;
		$this->assertEqual('application/octet-stream', $file->getType());
		$this->assertEqual('application', $file->getTypeDiscrete());
	}	
	
	function testGetTypeFromHeaderWithEncoding() {
		$file = new SvnOpenFile('/test/a.txt');
		$file->head = array(0 => 'HTTP/1.1 200 OK',
			'Content-Type' => 'text/html; charset=iso-8859-1');
		$file->headStatus = 200;
		$this->assertEqual('text/html', $file->getType());
		$this->assertEqual('text', $file->getTypeDiscrete());
	}
	
	function testInstantiateTwice() {
		$file = new SvnOpenFile('/test/a.txt');
		$this->expectError(new PatternExpectation('/already .* open/'));
		$file2 = new SvnOpenFile('/test/b.txt');
	}
	
	function testIsFolderNameLooksLikeFile() {
		$file = new SvnOpenFile('/test/a.folder/');
		// The below will give isFolder = false, but how to test?
		$file = new SvnOpenFile('/test/a.folder');
	}

}

testrun(new TestSvnOpenFile());
?>
