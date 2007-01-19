<?php
require(dirname(__FILE__)."/Presentation.class.php");
require("../lib/simpletest/setup.php");

class TestClazz {
}

class TestPresentation extends UnitTestCase {
	
	function TestPresentation() {
		$this->UnitTestCase();
	}
	
	function setUp() {
		// bypas the singleton check
		global $_presentationInstance;
		$_presentationInstance = null;
	}
	
	function testAddStylesheet() {
		$p = Presentation::getInstance();
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
	
	function testSingletonCreateTwice() {
		$p = Presentation::getInstance();
		$p2 = Presentation::getInstance();
		$this->assertReference($p, $p2);
	}

}

testrun(new TestPresentation());

?>