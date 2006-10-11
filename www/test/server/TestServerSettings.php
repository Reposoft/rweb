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
		$this->assertTrue(ini_get('display_errors'), "For internal use we don't want to risk losing error messages");
	}
	
	function testFileUploads() {
		$this->assertEqual(1, ini_get('file_uploads'), "File uploads should be allowed");
		$maxsize = ini_get('upload_max_filesize');
		$M = 1048576;
		eval(''.str_replace('M', ' * '.$M, '$max = '.$maxsize.';'));
		$this->assertTrue($max >= 10*$M, "10 MB file uploads must be allowed, but upload_max_filesize is $maxsize ($max bytes).");
	}

	function testUrlFopen() {
		$this->assertTrue(1, ini_get('allow_url_fopen'), "allow_url_fopen must be enabled");
		$this->assertTrue(ini_get('default_socket_timeout')<10, "default_socket_timeout should be no more than 10, to make the application responsive. We have local or near local access to all resources.");
	}

	function testDefaultEncoding() {
		$this->assertEqual("text/html", ini_get('default_mimetype'));
		$this->assertEqual("UTF-8", ini_get('default_charset'));
	}

	function testMbString() {
		$this->assertTrue(function_exists('mb_http_input'), "extension mb_string must be installed in php");
		$this->assertEqual("UTF-8", ini_get('mbstring.http_input'));
		$this->assertFalse(ini_get('mbstring.substitute_character'));
		$this->assertEqual("UTF-8", ini_get('mbstring.internal_encoding'));
		$this->assertEqual("Neutral", ini_get('mbstring.language'));
		$this->assertTrue(ini_get('mbstring.encoding_translation'));
		$this->assertEqual("auto", ini_get('mbstring.detect_order'));
	}
}

testrun(new TestServerSettings());

?>

