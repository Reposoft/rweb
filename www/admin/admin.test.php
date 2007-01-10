<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')

// import the script under test
require('admin.inc.php');
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

class TestAdmin extends UnitTestCase {

	function setUp() {
		
	}
	
	function testStartsWith() {
		$this->assertEqual(true, startsWith("hepp","") );
		$this->assertEqual(true, startsWith("hepp","he") );
		$this->assertEqual(true, startsWith("hepp","hepp") );
		$this->assertEqual(false, startsWith("","hepp") );
		$this->assertEqual(false, startsWith("hepp","hopp") );
		$this->assertEqual(false, startsWith("hep","hepp") );
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
	
	function testFormatRev() {
		$this->assertEqual( "0000012", formatRev(12) );
	}
	
	function testGetFilename() {
		$this->assertEqual( "svnrepo-foo-0000000-to-1234567", getFilename("svnrepo-foo-",0,1234567) );
	}
	
	function testGetRevisionInfo() {
		$info = getRevisionInfo("svnrepo-foo-0000000-to-1234567.dump.gz","svnrepo-foo-");
		$this->assertEqual( "svnrepo-foo-0000000-to-1234567.dump.gz", $info[0] );
		$this->assertEqual( 0, $info[1] );
		$this->assertEqual( 1234567, $info[2] );
	}
	
	function testGetBackupInfo() {
		$files = array(
			getFilename( getPrefix("/path/to/repo"), 3, 12),
			getFilename( getPrefix("/path/to/repo"), 100, 1000),
			getFilename( getPrefix("/path/to/repo"), 1111111, 1111111)
			);
		$revs = getbackupInfo( $files, getPrefix("/path/to/repo") );
		$this->assertEqual( getFilename( getPrefix("/path/to/repo"), 1111111, 1111111), $revs[2][0], "name of third file");
		$this->assertEqual( 3, $revs[0][1] );
		$this->assertEqual( 12, $revs[0][2] );
		$this->assertEqual( 100, $revs[1][1] );
		$this->assertEqual( 1000, $revs[1][2] );
		$this->assertEqual( 1111111, $revs[2][1] );
		$this->assertEqual( 1111111, $revs[2][2] );
	}
	
	function testGetDirContents() {
		$dir = getConfig("backup_folder");
		$prefix = getPrefix( getConfig("local_path") );
		$this->sendMessage("This test depends on file in backup_folder $dir, and local_path wich gives prefix $prefix");
		$files1 = getDirContents($dir);
		$total = count($files1);
		if ( $total<1 )
			$this->fail("No files found in $dir");
		$files2 = getDirContents($dir, $prefix);
		$filtered = count($files2);
		if ( $filtered<1 )
			$this->sendMessage("No files found in $dir with prefix $prefix");
		if ( $total < $filtered )
			$this->fail( "Filtering with prefix makes $total files become $filtered, which is very strange");
		if ( $total == $filtered )
			$this->sendMessage( "Total number of files is same as filtered, this may indicate that $prefix filtering makes no difference");
	}
	
}

testrun(new TestAdmin());
?>
