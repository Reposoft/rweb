<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')
function addPlugin($n) {}
// import the script under test
require('../validation/validation.inc.php');
require('edit.inc.php');
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../../lib/simpletest/setup.php");

class TestEditPlugin extends UnitTestCase {

	function setUp() {
		
	}
	
	// note that we should be resrictive with changing these rules
	// to avoide extra diffs in existing documents for new releases
	
	function testIndentHtmlTags() {
		$html = "<p>hello<br />world</p><p>!</p>";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("\n<p>hello\n<br />world</p>\n<p>!</p>", $result);
	}
	
	function testIndentHtmlTagsPclass() {
		$html = "a<p class=\"b\">b</p>";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("a\n<p class=\"b\">b</p>", $result);
	}

	function testIndentHtmlTagsA() {
		$html = "<p><a href=\"link\">link</a></p>";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("\n<p><a href=\"link\">link</a></p>", $result);
	}

	function testIndentHtmlTagsH() {
		$html = "<h1>1</h1><h2>2</h2><h3>3</h3>";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("\n<h1>1</h1>\n<h2>2</h2>\n<h3>3</h3>", $result);
	}

	function testIndentHtmlTagsSentence() {
		$html = "Vade. Retro. Adaces...";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("Vade. \nRetro. \nAdaces...", $html);
	}
	
	function testIndentHtmlTagsSentenceLowercase() {
		$html = "Vade. retro. adaces...";
		$result = editIndentHtmlDocument($html);
		// lowercase after dot means not beginning of a sentence
		$this->assertEqual("Vade. retro. adaces...", $html);
	}
	
	function testIndentHtmlTagsAbreviation() {
		$html = "Vade. e.Kr.x a.b. C.";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("Vade. e.Kr.x a.b. \nC.", $html);
	}
	
	function testIndentHtmlTagsSentenceNumber() {
		$html = "Vade. 2007";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("Vade. \n2007", $html);
	}
	
	function testIndentHtmlTagsSentenceTag() {
		$html = "Vade.<a href=\"link\">link</a>";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("Vade.\n<a href=\"link\">link</a>", $html);
	}

	function testIndentHtmlTagsSentenceAuml() {
		$this->sendMessage('TODO how do we handle non-ascii characters?');
		$html = "Non. &Auml;scii. &auml;";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("Non. \n&Auml;scii. &auml;", $html);
	}	

	function testIndentHtmlTagsSentenceQuestion() {
		$html = "What? No newline? yes";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("What? \nNo newline? yes", $html);
	}
	
	function testIndentHtmlTagsSentenceColon() {
		$html = "Verdict: working: Yes";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("Verdict: working: \nYes", $html);
	}
	
	function testIndentHtmlTagsSentenceExclamation() {
		$html = "Yes! no! Whatever.";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("Yes! no! \nWhatever.", $html);
	}
	
	function testPregMatch() {
		preg_match('/(a)(b)?(c)((?(2)d))/',
			'abcdefghij', $m);
		print_r($m);
	}
}

testrun(new TestEditPlugin());
?>
