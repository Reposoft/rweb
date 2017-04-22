<?php
require(dirname(__FILE__)."/RepositoryTree.class.php");
require("../lib/simpletest/setup.php");

// mock
function getRepository() {
	return 'http://where-we-work.com/my-repo/';
}
function getPathName($p) {
	return 'my-repo';
}

class TestRepositoryTree extends UnitTestCase {
	
	var $tempfile;
	var $tree;
	
	function TestRepositoryTree() {
		// empty constructor
	}
	
	function setUp() {
		$this->createNewFile();
	}
	
	function tearDown() {
		//deleteFile($this->tempfile);
		unlink($this->tempfile);
	}

	function createNewFile() {
		$this->tempfile = tempnam('does-not-exist-asldgjfdlkqjwrekjwer', "test-repos-access"); // TODO use standard repos behaviour
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
	
	function testEntryPoints() {
		$e = $this->tree->getEntryPoints();
		$this->assertEqual(2, count($e));
		$this->assertTrue(isset($e['/svensson']));
		$this->assertEqual(true, $e['/svensson']->isReadOnly());
		$this->assertEqual(false, $e['/svensson']->isByGroup());
		$this->assertTrue(isset($e['/aproject']));
		$this->assertEqual(false, $e['/aproject']->isReadOnly());
		$this->assertEqual(true, $e['/aproject']->isByGroup());	
	}
	
	function testEntryPointsDifferentUser() {
		$t = new RepositoryTree($this->tempfile, 'bengtsson');
		$this->assertEqual(0, count($t->getEntryPoints()));
	}
	
	function testDisplayname() {
		$e = $this->tree->getEntryPoints();
		$this->assertEqual('svensson', $e['/svensson']->getDisplayname());
	}
	
	function test_getEntryPointsForUserOrGroup() {
		$acl = array();
		$acl['/'] = array('admin' => 'rw');
		$acl['/sven'] = array('sven' => 'rw');
		$e = RepositoryTree::_getEntryPointsForUserOrGroup($acl, 'sven', array());
		$this->assertEqual(1, count($e));
		$this->assertEqual('/sven', $e['/sven']->getPath());
	}

	function test_getEntryPointsForUserOrGroup2() {
		$acl = array();
		$acl['/proj'] = array('@grupp' => 'r', '@boss' => 'rw');
		$acl['/proj/secret'] = array('@boss' => 'rw', '@grupp' => '' ); // access stopped for @grupp, no test for this yet
		$acl['/proj/a/subdir'] = array('sven' => 'rw');
		$e = RepositoryTree::_getEntryPointsForUserOrGroup($acl, 'sven', array('grupp'));
		$this->assertEqual(2, count($e));
		$this->assertEqual('/proj', $e['/proj']->getPath());
		$this->assertEqual(true, $e['/proj']->isReadOnly());
		$this->assertEqual(true, $e['/proj']->isByGroup());
		$this->assertEqual('/proj/a/subdir', $e['/proj/a/subdir']->getPath());
		$this->assertEqual(false, $e['/proj/a/subdir']->isReadOnly());
		$this->assertEqual(false, $e['/proj/a/subdir']->isByGroup());
	}
	
	function test_getEntryPointsWithAsterisk() {
		$acl = array();
		$acl['/'] = array('*' => 'r'); // the most basic acl
		$e = RepositoryTree::_getEntryPointsForUserOrGroup($acl, 'sven', array());
		$this->assertEqual(1, count($e));
		$this->assertEqual('', $e['/']->getPath()); // no other paths in ACL have tailing slash
		$this->assertEqual(true, $e['/']->isReadOnly());
		// for "/", displayname should be the repository name
		$this->assertEqual('my-repo', $e['/']->getDisplayname());
	}

	function test_getEntryPointsWithAsteriskReadOnlyTrunk() {
		$acl = array();
		$acl['/trunk'] = array('*' => 'rw');
		$e = RepositoryTree::_getEntryPointsForUserOrGroup($acl, 'sven', array());
		$this->assertEqual(1, count($e));
		$this->assertEqual('/trunk', $e['/trunk']->getPath());
		$this->assertEqual(false, $e['/trunk']->isReadOnly());
		// for "/", displayname should be the repository name
		$this->assertEqual('trunk', $e['/trunk']->getDisplayname());
	}	

	function test_getEntryPointsIgnoreReadOnlyPublicFiles() {
		$acl = array();
		//$acl['/sven'] = array('sven' => 'rw');
		$acl['/sven/public/xmlfile.xml'] = array('sven' => 'r'); // is obviously a folder
		// read only files is considered temporary shares, not a target for navigation
		$acl['/trunk/public/xmlfile.xml'] = array('*' => 'r');
		$e = RepositoryTree::_getEntryPointsForUserOrGroup($acl, 'sven', array());
		$this->assertEqual(1, count($e), "Don't list readonly resources that are accessible to everyone");
		$this->assertEqual('/sven/public/xmlfile.xml', $e['/sven/public/xmlfile.xml']->getPath());
	}
	
	function test_getEntryPointsSame() {
		$acl = array();
		$acl['/proj'] = array('@proj' => 'rw', 'test' => 'rw', 'test' => 'r');
		$e = RepositoryTree::_getEntryPointsForUserOrGroup($acl, 'test', array('proj'));
		$this->assertEqual(1, count($e), "2+ acl matches for the same path should be shown as one. %s");
		$this->assertEqual('/proj', $e['/proj']->getPath());
		$this->assertFalse($e['/proj']->isReadOnly(), "Should show the highet available access level. %s");
	}

	function test_getEntryPointsOverlap() {
		$acl = array();
		$acl['/proj'] = array('@aproject' => 'r', 'svensson' => 'rw');
		$e = RepositoryTree::_getEntryPointsForUserOrGroup($acl, 'svensson', array('aproject'));
		$this->assertEqual(1, count($e), "Two acl matches for the same path should be shown as one. %s");
		$this->assertEqual('/proj', $e['/proj']->getPath());
		$this->assertFalse($e['/proj']->isReadOnly(), "Should show the highet access level of the two. %s");
	}	
	
	// test the small entry point class too
	function testRepositoryEntryPoint() {
		$e = new RepositoryEntryPoint('/my/own/folder', 'rw', false);
		$this->assertEqual('/my/own/folder', $e->getPath());
		$this->assertEqual('folder', $e->getDisplayName());
		$this->assertEqual(false, $e->isReadOnly());
		$this->assertEqual(false, $e->isByGroup());
		
		$e = new RepositoryEntryPoint('/my', 'r', true);
		$this->assertEqual('/my', $e->getPath());
		$this->assertEqual('my', $e->getDisplayName());
		$this->assertEqual(true, $e->isReadOnly());
		$this->assertEqual(true, $e->isByGroup());
	}
	
}
    
testrun(new TestRepositoryTree());
