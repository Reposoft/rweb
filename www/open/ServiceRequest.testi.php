<?php
/**
 * Runs service requests in the testrepo structure
 */

// import the script under test
require('ServiceRequest.class.php');
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

define('TESTREPO', getSelfRoot().'/testrepo/');

class TestServiceRequestIntegration extends UnitTestCase {

	function setUp() {
		
	}

	function testFolder() {
		$url = TESTREPO.'/demoproject/trunk/public/documents/';
		$s = new ServiceRequest($url, array(), false);
		$s->exec();
		$this->assertEqual('200', $s->getStatus());
	}
	
	function testFolderRedirect() {
		$url = TESTREPO.'/demoproject/trunk/public/documents';
		$s = new ServiceRequest($url, array(), false);
		$s->exec();
		$this->assertEqual('301', $s->getStatus());
		$headers = $s->getResponseHeaders();
		$this->assertEqual("$url/", $headers['Location']);
	}

	function testFile() {
		$url = TESTREPO.'/demoproject/trunk/public/xmlfile.xml';
		$s = new ServiceRequest($url, array(), false);
		$s->exec();
		$this->assertEqual('200', $s->getStatus());
	}	
	
	function testFolderSpace() {
		$url = TESTREPO.'/demoproject/trunk/public/documents/legacy formats/';
		$s = new ServiceRequest($url, array(), false);
		$s->exec();
		$this->assertEqual('200', $s->getStatus());
	}
	
	function testFilePercent() {
		$url = TESTREPO.'/demoproject/trunk/public/documents/100%open.odt';
		$s = new ServiceRequest($url, array(), false);
		$s->exec();
		$this->assertEqual('200', $s->getStatus());
	}	
	
	
}

testrun(new TestServiceRequestIntegration());
?>
