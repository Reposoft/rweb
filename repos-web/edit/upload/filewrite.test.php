<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require("filewrite.inc.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../../lib/simpletest/setup.php");

class TestFilewrite extends UnitTestCase {

	function setUp() {
		
	}
	
	function testWriteNewTextFile() {
		$tmp = System::getTempFile('uploadtest');
		$text = "a";
		editWriteNewVersion_txt($text, $tmp);
		clearstatcache();
		$this->assertEqual(1+strlen(EDIT_DEFAULT_NEWLINE), filesize($tmp), "Should have written contents with newline at end of file. %s");
		System::deleteFile($tmp);
	}

	function testWriteNewTextFileNL() {
		$tmp = System::getTempFile('uploadtest');
		$text = "a\nb\nc";
		editWriteNewVersion_txt($text, $tmp);
		clearstatcache();
		$this->assertEqual(5+1, filesize($tmp), "The newline at end of file should also be LF. %s");
		System::deleteFile($tmp);
	}	
	
	function testWriteNewTextFileCRLF() {
		$tmp = System::getTempFile('uploadtest');
		$text = "a\r\nb\r\nc";
		editWriteNewVersion_txt($text, $tmp);
		clearstatcache();
		$this->assertEqual(7+2, filesize($tmp), "The newline at end of file should also be CR LF. %s");
		System::deleteFile($tmp);
	}	
	
	function testWriteNewVersionLF() {
		$tmp = System::getTempFile('uploadtest');
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "a\nb");
		$text = "a\nb\nc";
		editWriteNewVersion_txt($text, $tmp);
		clearstatcache();
		$this->assertEqual(6, filesize($tmp), "Should have written new version with newline at end of file. %s");
		System::deleteFile($tmp);
	}
	
	function testWriteNewVersionCRLF() {
		$tmp = System::getTempFile('uploadtest');
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "a\r\n");
		$text = "a\nb\nc";
		editWriteNewVersion_txt($text, $tmp);
		clearstatcache();
		$this->assertEqual(6+3, filesize($tmp), "Should have used the original newline type. %s");	
		// mixed newline types in new text
		$text = "a\nb\r\nc";
		editWriteNewVersion_txt($text, $tmp);
		$this->assertEqual(6+3, filesize($tmp), "Watch out for search and replace mistakes. %s");
		System::deleteFile($tmp);	
		// mixed newline types in original: not handled
	}
	
	function testNoNewlineInExisting() {
		$tmp = System::getTempFile('uploadtest');
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "abc");
		$text = "abc\nd";
		editWriteNewVersion_txt($text, $tmp);
		clearstatcache();
		$this->assertEqual(5+1, filesize($tmp), "Should have used the same newline type as in posted text. %s");	
	}

	function testNoNewlineInExistingCRLF() {
		$tmp = System::getTempFile('uploadtest');
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "abc");
		$text = "abc\r\nd";
		editWriteNewVersion_txt($text, $tmp);
		clearstatcache();
		$this->assertEqual(6+2, filesize($tmp), "Should have used the same newline type as in posted text. %s");	
	}	

	function testGetNewlineType() {
		$tmp = System::getTempFile('uploadtest');
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "abc\nd");
		$this->assertEqual("\n", getNewlineType($tmp));
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "abc\r");
		$this->assertEqual("\r", getNewlineType($tmp));
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "abc\r\nd");
		$this->assertEqual("\r\n", getNewlineType($tmp));
	}
	
	function testGetNewlineTypeDefault() {
		$tmp = System::getTempFile('uploadtest');
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "");
		$this->assertEqual(false, getNewlineType($tmp));
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "ABC\tE");
		$this->assertEqual(false, getNewlineType($tmp));
	}
	
	function testGetNewlineTypeInconsistent() {
		$tmp = System::getTempFile('uploadtest');
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "abc\r\nX\n\n");
		$this->assertEqual("\r\n", getNewlineType($tmp));
		System::deleteFile($tmp);
		System::createFileWithContents($tmp, $text = "abc\n\r\nX\r\n");
		$this->assertEqual("\n", getNewlineType($tmp));		
	}
	
}

testrun(new TestFilewrite());
?>
