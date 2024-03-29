<?php
// mock before include
function setupResponse() {
}
require(dirname(__FILE__)."/Presentation.class.php");
require("../lib/simpletest/setup.php");

class TestClazz {
}

class TestPresentation extends UnitTestCase {

	function __construct() {
		// empty constructor
	}

	function setUp() {
		// bypas the singleton check
		global $_presentationInstance;
		$_presentationInstance = null;
	}

	function testSmartyParameters() {
		$debugMode = false;//no config entry for this in 1.2 //getConfig('disable_caching');
		$p = new Presentation();
		$smarty = $p->smarty;
		if ($debugMode) {
			$this->assertFalse($smarty->caching);
		} else {
			$this->assertFalse($smarty->caching, "We should never cache output. %s");
			$this->assertFalse($smarty->force_compile);
		}
	}

	function testAddStylesheet() {
		$p = Presentation::getInstance();
		$p->addStylesheet('repository/repository.css');
		$head = $p->_getHeadTags('http://localhost/webapp/', '/mytheme/style/');
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
	
	function testPrefilter_urlRewriteForHttps_simple() {
		$result = Presentation_urlRewriteForHttps(
			'<a href="{=$repository}/">{=$repository}/</a>', $smarty);
		$this->assertEqual('<a href="{=$repository|asLink}/">{=$repository}/</a>', $result);
	}

	function testPrefilter_urlRewriteForHttps() {
		$smarty = null;
		// no link
		$result = Presentation_urlRewriteForHttps(
			'http://'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'/', $smarty);
		$this->assertEqual('http://'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'/', $result);
		// href
		$result = Presentation_urlRewriteForHttps(
			'<a id="test" href="'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a id="test" href="'.LEFT_DELIMITER.'$var|asLink'.RIGHT_DELIMITER.'">', $result);
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
		// associative array syntax
		$result = Presentation_urlRewriteForHttps(
			'<a href="'.LEFT_DELIMITER.'$var.field'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a href="'.LEFT_DELIMITER.'$var.field|asLink'.RIGHT_DELIMITER.'">', $result);
		// object syntax, _not_ supported
		$result = Presentation_urlRewriteForHttps(
			'<a href="'.LEFT_DELIMITER.'$var->isOk()'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a href="'.LEFT_DELIMITER.'$var->isOk()'.RIGHT_DELIMITER.'">', $result);
		// our own object syntax -- this filter must be applied _before_ object syntax filter
		$result = Presentation_urlRewriteForHttps(
			'<a href="'.LEFT_DELIMITER.'$file,folderUrl'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a href="'.LEFT_DELIMITER.'$file,folderUrl|asLink'.RIGHT_DELIMITER.'">', $result);
	}

	function testPrefilter_urlRewriteForHttps_object() {
		$result = Presentation_urlRewriteForHttps(
			'<a id="repository" href="{=$file,folderUrl}">return to repository</a>
			{=$file->getkind2()|ucfirst}: <a class="{=$file->getkind2()}" href="{=$file,url}">', $smarty);
		$this->assertEqual(
			'<a id="repository" href="{=$file,folderUrl|asLink}">return to repository</a>
			{=$file->getkind2()|ucfirst}: <a class="{=$file->getkind2()}" href="{=$file,url|asLink}">', $result);
	}

	function testPrefilter_removeIndentation() {
		$smarty = null;
		// only newline
		$result = Presentation_removeIndentation(
			"abc</p>\n<p>def", $smarty);
		$this->assertEqual("abc</p><p>def", $result);
		// CRLF
		$result = Presentation_removeIndentation(
			"abc</p>\r\n<p>def", $smarty);
		$this->assertEqual("abc</p><p>def", $result);
		// newline and indent spaces
		$result = Presentation_removeIndentation(
			"abc</p>\n   <p>def", $smarty);
		$this->assertEqual("abc</p><p>def", $result);
		// newline and indent tab
		$result = Presentation_removeIndentation(
			"abc</p>\n\t\t<p>def", $smarty);
		$this->assertEqual("abc</p><p>def", $result);
		// template varable
		$result = Presentation_removeIndentation(
			"abc</p>\n   {=if \$a}", $smarty);
		$this->assertEqual("abc</p>{=if \$a}", $result);
	}

	function testPrefilter_urlEncodeQueryString() {
		$result = Presentation_urlEncodeQueryString(
			'<a id="test" href="edit/?target='.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a id="test" href="edit/?target='.LEFT_DELIMITER
			.'$var|rawurlencode'.RIGHT_DELIMITER.'">', $result);
		/* only encode 'target' param
		$result = Presentation_urlEncodeQueryString(
			'<a id="test" href="?'.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a id="test" href="?'.LEFT_DELIMITER
			.'$var|rawurlencode'.RIGHT_DELIMITER.'">', $result);
		*/
		$result = Presentation_urlEncodeQueryString(
			'<a id="test" href="?target='.LEFT_DELIMITER.'$var|already'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a id="test" href="?target='.LEFT_DELIMITER
			.'$var|already'.RIGHT_DELIMITER.'">', $result);

		$result = Presentation_urlEncodeQueryString(
			'<a id="test" href="../?target='.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER
				.'&rev='.LEFT_DELIMITER.'$rev'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a id="test" href="../?target='.LEFT_DELIMITER.'$var|rawurlencode'.RIGHT_DELIMITER
				.'&rev='.LEFT_DELIMITER.'$rev'.RIGHT_DELIMITER.'">', $result);

		$result = Presentation_urlEncodeQueryString(
			'<a id="test" href="?rev='.LEFT_DELIMITER.'$rev'.RIGHT_DELIMITER
				.'&target='.LEFT_DELIMITER.'$target'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a id="test" href="?rev='.LEFT_DELIMITER.'$rev'.RIGHT_DELIMITER
				.'&target='.LEFT_DELIMITER.'$target|rawurlencode'.RIGHT_DELIMITER.'">', $result);

		// support our own object notation
		$result = Presentation_urlEncodeQueryString(
			'<a id="test" href="?rev='.LEFT_DELIMITER.'$rev'.RIGHT_DELIMITER
				.'&target='.LEFT_DELIMITER.'$object,property'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a id="test" href="?rev='.LEFT_DELIMITER.'$rev'.RIGHT_DELIMITER
				.'&target='.LEFT_DELIMITER.'$object,property|rawurlencode'.RIGHT_DELIMITER.'">', $result);	
		// don't care if the url continues after first template variable
		$result = Presentation_urlEncodeQueryString(
			'<a href="{=$webapp}open/?target='.LEFT_DELIMITER.'$entry,path'.RIGHT_DELIMITER
				.'/'.LEFT_DELIMITER.'=$toolpath'.RIGHT_DELIMITER.'" ', $smarty);
		$this->assertEqual(	'<a href="{=$webapp}open/?target='.LEFT_DELIMITER.'$entry,path|rawurlencode'.RIGHT_DELIMITER
				.'/'.LEFT_DELIMITER.'=$toolpath'.RIGHT_DELIMITER.'" ', $result);	
	}

	function testPrefilter_supportRepoBase() {
		$result = Presentation_supportRepoBase(
			'<a id="test" href="edit/?target='.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a id="test" href="edit/?base={=$base}&target='.LEFT_DELIMITER
			.'$var'.RIGHT_DELIMITER.'">', $result);

		// target does not need to be the first query param
		$result = Presentation_supportRepoBase(
			'<a id="test" href="edit/?rev=X&target='.LEFT_DELIMITER.'$var'.RIGHT_DELIMITER.'">', $smarty);
		$this->assertEqual('<a id="test" href="edit/?base={=$base}&rev=X&target='.LEFT_DELIMITER
			.'$var'.RIGHT_DELIMITER.'">', $result);

		// forms
		$result = Presentation_supportRepoBase(
			'<form><input type="hidden" name="target" value="dontcare"/></form>', $smarty);
		// TODO the filters under test are not delimiter indpendent, so why should all the tests be so
		$this->assertEqual('<form><input type="hidden" name="base" value="'
			.LEFT_DELIMITER.'$base'.RIGHT_DELIMITER.
			'"/><input type="hidden" name="target" value="dontcare"/></form>', $result);
	}

	function testGetFileId() {
		$this->assertEqual(getFileId('aB'), 'aB', 'Preserve case. %s');
		$this->assertEqual(getFileId('a.-_B'), 'a.-_B', 'Preserve dot dash underscore. %s');
		$this->assertEqual(getFileId('a:B'), 'a:B', 'Preserve colon. %s');
		$this->assertEqual(getFileId('a%b'), 'a_25b', 'precent -> urlencode -> replace encode-percent with _. %s');
		$this->assertEqual(getFileId('a b'), 'a_20b', 'precent -> urlencode -> replace encode-percent with _. %s');
		$this->assertEqual(getFileId('a*'), 'a_2a', 'escape codes should be lower case as in subversion. %s');
	}

	function testGetFileIdUtf8() {
		// difficult to test unless we know that this php file is UTF-8 encoded
		$s = "h\xc3\xa5-\xc3\x85.txt";
		$this->assertEqual(getFileId($s), 'h_c3_a5-_c3_85.txt');
	}

}

$testcase = new TestPresentation();
testrun($testcase);

?>
