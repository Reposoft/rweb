<?php

/*	assertTrue($x)	Fail if $x is false
	assertFalse($x)	Fail if $x is true
	assertNull($x)	Fail if $x is set
	assertNotNull($x)	Fail if $x not set
	assertIsA($x, $t)	Fail if $x is not the class or type $t
	assertNotA($x, $t)	Fail if $x is of the class or type $t
	assertEqual($x, $y)	Fail if $x == $y is false
	assertNotEqual($x, $y)	Fail if $x == $y is true
	assertIdentical($x, $y)	Fail if $x == $y is false or a type mismatch
	assertNotIdentical($x, $y)	Fail if $x == $y is true and types match
	assertReference($x, $y)	Fail unless $x and $y are the same variable
	assertCopy($x, $y)	Fail if $x and $y are the same variable
	assertWantedPattern($p, $x)	Fail unless the regex $p matches $x
	assertNoUnwantedPattern($p, $x)	Fail if the regex $p matches $x
	assertNoErrors()	Fail if any PHP error occoured
	assertError($x)	Fail if no PHP error or incorrect message
	assertErrorPattern($p)	Fail unless the error matches the regex $p 
*/

require("../../lib/simpletest/setup.php");

class TestServerSettings extends UnitTestCase {

	function testMagicQuotes() {
		$this->assertEqual(0, get_magic_quotes_gpc(), "PHP magic_quotes_gpc should be OFF");
	}

	function testErrorReporting() {
		//$this->assertFalse(ini_get('display_errors'), "We handle the errors ourselves, and if we don't php should not print them as HTML");
		$this->assertTrue(ini_get('display_errors'), "display_errors: For internal use we don't want to risk losing error messages. %s");
	}
	
	function testFileUploads() {
		$this->assertEqual(1, ini_get('file_uploads'), "file_uploads: File uploads should be allowed");
		$maxsize = ini_get('upload_max_filesize');
		$M = 1048576;
		eval(''.str_replace('M', ' * '.$M, '$max = '.$maxsize.';'));
		$this->assertTrue($max >= 10*$M, "upload_max_filesize: 10 MB file uploads must be allowed, but upload_max_filesize is $maxsize ($max bytes).");
	}

	function testUrlFopen() {
		// we should use the ServiceRequest class (or cURL extension directly) but some scripts still use url fopen
		$this->assertTrue(1, ini_get('allow_url_fopen'), "allow_url_fopen: Must be enabled");
		// not needed anymore, use curl for calls:
		//$this->assertTrue(ini_get('default_socket_timeout')<=10, "default_socket_timeout: Should be no more than 10, to make the application responsive. We have local or near local access to all resources.");
	}
	
	function testOutputBuffering() {
		$this->assertEqual(0, ini_get('output_buffering'), "output_buffering: For passthrough of resources and reports we don't want output buffering. %s");
	}

	function testDefaultEncoding() {
		$this->assertEqual("text/html", ini_get('default_mimetype'), "default_mimetype: %s");
		$this->assertEqual("UTF-8", ini_get('default_charset'), "default_charser: %s");
	}

	function testMbString() {
		$this->assertEqual("UTF-8", ini_get('mbstring.internal_encoding'), "mbstring.internal_encoding: %s");
		$this->assertEqual("Neutral", ini_get('mbstring.language'), "mbstring.language: %s");
		$this->assertEqual("auto", ini_get('mbstring.detect_order'), "mbstring.detect_order: %s");
		$this->assertFalse(ini_get('mbstring.substitute_character'), "mbstring.substitute_character: Input that can't be converted should cause error message. %s");
		$this->assertEqual(0, ini_get('mbstring.func_overload'), "mbstring.func_overload: Mb should be called explicitly, not silently. %s");
		// expecting UTF-8 input, but no automatic input conversion because input from browser should be valid already (if not, we throw an error)
		$this->assertEqual("UTF-8", ini_get('mbstring.http_input'), "mbstring.http_input: %s");
		$this->assertFalse(ini_get('mbstring.encoding_translation'), "mbstring.encoding_translation: Automatic encoding translation (mbstring.encoding_translation) should be Off. %s");
		// no automatic output conversion
		$this->assertEqual("", ini_get('output_handler'), "output_handler: No output handler should be set, because default is to not buffer output. %s");
		$this->assertEqual(0, ini_get('mbstring.http_output'), "mbstring.http_output: mbstring is not the output handler, so there is no need for a http_output setting. %s");
	}
}

testrun(new TestServerSettings());

?>

