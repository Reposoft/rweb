<?php
require(dirname(__FILE__)."/Presentation.class.php");
require("../lib/simpletest/setup.php");

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
	
	function testSingletonCreateTwice() {
		$p = new Presentation();
		$this->expectError('Code error. An attempt was made to create a second page instance.');
		$p2 = new Presentation();
	}

}

testrun(new TestPresentation());

?>