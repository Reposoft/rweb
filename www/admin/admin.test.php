<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require('admin.inc.php');
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

class TestAdmin extends UnitTestCase {

	function setUp() {
		
	}
	
	function testGetRepositoryName() {
		$this->assertEqual('testrepo', getRepositoryName('/testrepo/repo/'));
		$this->assertEqual('testrepo', getRepositoryName('/testrepo/repo'));
		$this->assertEqual('testrepo', getRepositoryName('C:\testrepo\repo'));
		$this->assertEqual('testrepo2', getRepositoryName('/repos/testrepo2/'));
		$this->assertEqual('testrepo', getRepositoryName('/testrepo/Repo/'));
	}
	
	function testGetRepositoryNameDefault() {
		$this->assertEqual('repo-repo', getRepositoryName('/repo/repo/'));
		$this->assertEqual('repo-repo', getRepositoryName('C:\repo\repo'));
	}
	
	function testGetRepositoryNameSpaces() {
		$this->assertEqual('test_repo', getRepositoryName('/repos/test repo/'));
	}
	
	function testGetRepositoryNameUppercase() {
		$this->assertEqual('test_repo', getRepositoryName('/repos/Test Repo/Repo'));
	}
	
}

testrun(new TestAdmin());
?>
