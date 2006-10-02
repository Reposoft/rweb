<?php
require("../lib/simpletest/setup.php");

require(dirname(__FILE__)."/Presentation.class.php");

class TestPresentation extends UnitTestCase {

	function TestPresentation() {
		$this->UnitTestCase();
	}
	
	function testAddStylesheet() {
		$p = new Presentation();
		$p->addStylesheet('repository/repository.css');
		$head = $p->_getAllHeadTags('/mytheme/style/');
		$x = strpos($head, 'mytheme/style/repository/repository.css');
		$this->assertTrue($x > 0);
		$this->assertNoErrors();
	}

	function testErrorHandler() {
		$this->assertEqual(function_exists('reportErrorToUser'),
			"Presentation class should define a custom error reporting function as defined in repos.properties.php");
	}
	
	function testFilenameRule() {
		$r = new FilenameRule('file');
		$this->assertNull($r->validate('abc.txt'));
		$this->assertNull($r->validate(''));
		$this->assertNull($r->validate(str_repeat('a', 50)));
		$this->assertNotNull($r->validate(str_repeat('a', 51)), "max length 50");
		$this->sendMessage("Message on validate 'a\"': ".$r->validate('a"'));
		$this->assertNotNull($r->validate('a"'), 'double quote not allowed in filename');
		$this->assertNotNull($r->validate('a*'), '* not allowed in filename');
	}
}

testrun(new TestPresentation());

?>