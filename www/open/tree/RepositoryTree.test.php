<?php
require("../../lib/simpletest/setup.php");

require(dirname(__FILE__)."/RepositoryTree.class.php");

class TestRepositoryTree extends UnitTestCase {
	
	var $tempfile;
	var $tree;
	
	function TestRepositoryTree() {
		$this->UnitTestCase();
	}
	
	function setUp() {
		$this->createNewFile();
	}
	
	function tearDown() {
		unlink($this->tempfile);
	}

	function createNewFile() {
		$this->tempfile = tempnam("/tmp", "test-repos-access");
		$handle = fopen($this->tempfile, "w");
		fwrite($handle, "[groups]\n");
		fwrite($handle, "aproject =  svensson, test\n");
		fwrite($handle, "2project = test, svensson ,bengtsson\n");
		fwrite($handle, "3project = test, bengtsson,svensson\n");
		fwrite($handle, "anotherproject = bengtsson, svensson2, test\n");
		fwrite($handle, "anotherproject2 = asvensson, bsvensson\n");
		fwrite($handle, "\n");
		fwrite($handle, "[/]\n");
		fwrite($handle, "\n");
		fwrite($handle, "[/svensson]\n");
		fwrite($handle, "svensson = r\n");
		fwrite($handle, "\n");
		fwrite($handle, "[/test]\n");
		fwrite($handle, "test = rw\n");
		fwrite($handle, "\n");
		fwrite($handle, "[/aproject]\n");
		fwrite($handle, "@aproject = rw\n");
		fclose($handle);
		
		$this->tree = new RepositoryTree($this->tempfile, 'svensson');
	}
	
	function testGroups() {
		$groups = $this->tree->getGroups();
		$this->assertEqual(3, count($groups));
		$this->assertEqual('aproject', $groups[0]);
		$this->assertEqual('2project', $groups[1]);
		$this->assertEqual('3project', $groups[2]);
	}
	
	
	
	
	// test the small entry point class too
	function testRepositoryEntryPoint() {
		$e = new RepositoryEntryPoint('/my/own/folder');
		$this->assertEqual('/my/own/folder', $e->getPath());
		$this->assertEqual('folder', $e->getDisplayName());
		$e->setReadOnly(true);
		$this->assertEqual(true, $e->isReadOnly());
	}
	
}
    
$test = &new TestRepositoryTree();
$test->run(new HtmlReporter());
