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

}

$test = &new TestPresentation();
$test->run(new HtmlReporter());

?>