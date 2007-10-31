<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require("syntax.inc.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../../lib/simpletest/setup.php");

class TestSyntaxInclude extends UnitTestCase {
	
	function testCore() {
		$load = syntax_getHeadTags('w');
		$find = preg_grep('/shCore\.js/', $load);
		$this->assertEqual(1, count($find));
	}

	function testPhp() {
		$_SERVER['REQUEST_URI'] = '/repos/open/file/?target=/a.php';
		$load = syntax_getHeadTags('w');
		$this->sendMessage($load);
		$find = preg_grep('/shBrushPhp\.js/', $load);
		$this->assertEqual(1, count($find));
	}

	function testHtml() {
		$_SERVER['REQUEST_URI'] = '/repos/open/file/?target=/demoproject/trunk/public/website/index.html&rev=';
		$load = syntax_getHeadTags('w');
		$this->sendMessage($load);
		$find = preg_grep('/shBrushXml\.js/', $load);
		$this->assertEqual(1, count($find));
	}	

	function testDiff() {
		$_SERVER['REQUEST_URI'] = '/repos/open/diff/?target=/a.html&rev=b';
		$load = syntax_getHeadTags('w');
		$this->sendMessage($load);
		$find = preg_grep('/shCore\.js/', $load);
		$this->assertEqual(1, count($find));
		$find = preg_grep('/shBrushDiff\.js/', $load);
		$this->assertEqual(1, count($find));
	}	
	
}

testrun(new TestSyntaxInclude());
?>
