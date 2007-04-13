<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')
function addPlugin($n) {}
// import the script under test
require('../validation/validation.inc.php');
require('edit.inc.php');
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../../lib/simpletest/setup.php");

// mock
$written = null;
function editWriteNewVersion_txt($postedText, $destinationFile) {
	global $written;
	$written = $postedText;
}
function getLastWrite() {
	global $written;
	return $written;
}
class SmartyMock {
	function assign($a, $b) {}
	function fetch($t) {
		return '<html><body>a</body></html>';
	}
}
function smarty_getInstance() {
	return new SmartyMock();
}

class TestEditPlugin extends UnitTestCase {

	function setUp() {
		
	}
	
	// note that we should be resrictive with changing these rules
	// to avoid extra diffs in existing documents for new releases
	
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

	function testIndentHtmlTagsList() {
		$html = "<ol><li>1</li></ol><ul><li>2</li></ul>";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("\n<ol>\n<li>1</li>\n</ol>\n<ul>\n<li>2</li>\n</ul>", $result);
	}	
	
	function testIndentHtmlTagsSentence() {
		$html = "Vade. Retro. Adaces...";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("Vade. \nRetro. \nAdaces...", $html);
	}

	// when opening an indented document, the added newlines may be interpreted as spaces
	// we should have one space after each sentence (just like html treats spaces)
	function testIndentHtmlTagsSentenceExtraSpaces() {
		$html = "Vade.  Retro.  \nAdaces...";
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
		$this->assertEqual("Vade. \n<a href=\"link\">link</a>", $html);
	}

	function testIndentHtmlTagsSentenceAuml() {
		$this->sendMessage('TODO how do we handle non-ascii characters?');
		$html = "Non. &Auml;scii. &auml;";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("Non. \n&Auml;scii. &auml;", $html);
	}	
	
	function testIndentHtmlTagsSentenceEndWithUmlaut() {
		$html = "P&aring;. &Aring;r och dag.";
		$result = editIndentHtmlDocument($html);
		$this->assertEqual("P&aring;. \n&Aring;r och dag.", $html);
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
	
	function testCreateHtmlDocumentFromEditor() {
		$html = '<p>hello</p>';
		editWriteNewVersion_html($html, null);
		$result = getLastWrite();
		$this->assertTrue(strContains($result,'<html'));
		$this->assertTrue(strContains($result,'<body'));
		$this->assertTrue(strContains($result,'</body>'));
		$this->assertTrue(strContains($result,'</html>'));
	}

	function testCreateHtmlDocument() {
		$html = "<html htmlns=..>\n<body class=..>\n<p>hello</p>\n</body></html>";
		editWriteNewVersion_html($html, null);
		$result = getLastWrite();
		$this->assertEqual(1, substr_count($result,'<html'));
		$this->assertEqual(1, substr_count($result,'<body'));
		$this->assertEqual(1, substr_count($result,'</body>'));
		$this->assertEqual(1, substr_count($result,'</html>'));
	}

	function testCreateHtmlDocumentNoEndTags() {
		// should not create new document if html and body start tags exist
		$html = "<html>\n<body>\n<p>hello</p>\n";
		editWriteNewVersion_html($html, null);
		$result = getLastWrite();
		$this->assertEqual(1, substr_count($result,'<html'));
		$this->assertEqual(1, substr_count($result,'<body'));
		$this->assertEqual(0, substr_count($result,'</body>'));
		$this->assertEqual(0, substr_count($result,'</html>'));
	}
	
	function testFullDocument() {
		$html =
'<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></meta>
<meta name="Generator" content="Repos"></meta>
<title></title>
<link href="/home/documents.css" rel="stylesheet" type="text/css"></link>
</head>
<body>
<p>tjoff</p>

</body>
</html>';
		editWriteNewVersion_html($html, null);
		$result = getLastWrite();
		$this->assertEqual(1, substr_count($result,'<html'));
		$this->assertEqual(1, substr_count($result,'<body'));
		$this->assertEqual(1, substr_count($result,'</body>'));
		$this->assertEqual(1, substr_count($result,'</html>'));		
	}
}

testrun(new TestEditPlugin());
?>
