<?php
// mock before include
function setupResponse() {
}
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
	
	function testSmartyParameters() {
		$debugMode = getConfig('disable_caching');
		$p = new Presentation();
		$smarty = $p->smarty;
		if ($debugMode) {
			
		} else {
			$this->assertTrue($smarty->caching);
			$this->assertFalse($smarty->force_compile);
		}
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
		// assertion method not reliable: $this->assertReference($p, $p2);
		$this->assertTrue($p === $p2);
	}
	
	function testPrefilter_urlRewriteForHttps() {
		$smarty = null;
		// no link
		$result = Presentation_urlRewriteForHttps(
			'http://'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'/', $smarty);
		$this->assertEqual('http://'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'/', $result);
		// href
		$result = Presentation_urlRewriteForHttps(
			'<a href="'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a href="'.LEFT_DELIMITER.'$var|asLink'.RIGHT_DELIMITER.'">', $result);
		// part of href
		$result = Presentation_urlRewriteForHttps(
			'<a href="http://'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a href="http://'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'">', $result);
		// src
		$result = Presentation_urlRewriteForHttps(
			'<img src="'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<img src="'.LEFT_DELIMITER.'$var|asLink'.RIGHT_DELIMITER.'">', $result);
		// already a function, assume that the result is not a complete URL
		$result = Presentation_urlRewriteForHttps(
			'<a href="'.LEFT_DELIMITER.'$var|getPathName'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a href="'.LEFT_DELIMITER.'$var|getPathName'.RIGHT_DELIMITER.'">', $result);		
	}

}

testrun(new TestPresentation());

?>