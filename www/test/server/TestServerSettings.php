<?php

require("../../lib/simpletest/setup.php");

class TestServerSettings extends UnitTestCase {
	
	function testMagicQuotes() {
		$this->assertEqual(0, get_magic_quotes_gpc(), "PHP magic_quotes_gpc should be OFF");
	}
	
	function testFileUploads() {
		$this->assertEqual(1, ini_get('file_uploads'), "File uploads should be allowed");
		$maxsize = ini_get('upload_max_filesize');
		$M = 1048576;
		$this->assertTrue($maxsize > 10*$M, "10 MB file uploads must be allowed, but upload_max_filesize is $maxsize.");
	}
	
	function testUrlFopen() {
		$this->assertTrue(1, ini_get('allow_url_fopen'), "allow_url_fopen must be enabled");
	}
}

testrun(new TestServerSettings());

?>