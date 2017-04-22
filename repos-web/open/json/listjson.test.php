<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require('listjson.php');
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../../lib/simpletest/setup.php");

class TestListJson extends UnitTestCase {

	function setUp() {
		
	}
	
	function testFromXml() {
		$list = '<?xml version="1.0"?>
<lists>
<list
   path="http://localhost/data/demoproject/trunk/public/website">
<entry
   kind="dir">
<name>images</name>
<commit
   revision="3">
<author>test</author>
<date>2007-11-07T12:29:38.187657Z</date>
</commit>
</entry>
<entry
   kind="file">
<name>index.html</name>
<size>334</size>
<commit
   revision="1">
<author>admin</author>
<date>2007-11-07T12:29:34.958394Z</date>
</commit>
</entry>
<entry
   kind="file">
<name>styles.css</name>
<size>128</size>
<commit
   revision="1">
<author>admin</author>
<date>2007-11-07T12:29:34.958394Z</date>
</commit>
</entry>
</list>
</lists>';
		$json = getListJsonFromXml($list);
		$this->dump(null, $json);
		$this->assertTrue($json, 'should have been converted to json. %s');
		$this->assertTrue(strpos($json,'2007-11-07T12:29:34.95839'), 'json should include date. %s');
		// now create array without going into syntax details on the json
		$a = getListArrayFromJson($json);
		$this->assertTrue($a, 'JSON parser should have returned something');
		$this->assertTrue(is_array($a), 'JSON parser should have returned an array');
	}
	
	function testArray() {
		// taken from getListJsonFromXml output
		$json = '{"path":"http://localhost/data/demoproject/trunk/public/website", "list":{
"images":{
"commit":{
"author":"test","date":"2007-11-07T12:29:38.187657Z","revision":"3"},
"kind":"dir"},
"index.html":{
"size":"334","commit":{
"author":"admin","date":"2007-11-07T12:29:34.958394Z","revision":"1"},
"kind":"file"},
"styles.css":{
"size":"128","commit":{
"author":"admin","date":"2007-11-07T12:29:34.958394Z","revision":"1"},
"kind":"file"}
}}';
		$a = getListArrayFromJson($json);
		$this->assertTrue(is_array($a), 'Should get a php array. %s');
		$this->assertTrue(count($a)>0, count($a).' entries in resulting array. %s');
		if (function_exists('json_decode')) {
			$php52 = json_decode($json, true);
			$this->assertEqual($a,$php52);
		}
	}
	
}

$testcase = new TestListJson();
testrun($testcase);
?>
