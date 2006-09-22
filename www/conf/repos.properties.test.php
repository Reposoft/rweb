<?php
require_once(dirname(__FILE__).'/repos.properties.php');
require("../lib/simpletest/setup.php");

class TestReposProperties extends UnitTestCase {

	function TestReposProperties() {
		$this->UnitTestCase();
	}

	function testGetTempDir() {
		$dir = getTempDir();
		$this->assertTrue(file_exists($dir));
		$this->assertTrue(is_writable($dir));
	}
	
}

$test = &new TestReposProperties();
$test->run(new HtmlReporter());

?>
