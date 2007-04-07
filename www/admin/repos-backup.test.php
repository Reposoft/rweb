<?php
// define any mock behaviour, the script under test might have "if(!function_defined('F') require('F.php')
// currently we need a mock report object, because the admin output design sucks
class MockReport {
	function debug($m) {}
	function error($m) {}
	function fatal($m) {}
}
$report = new MockReport();
// import the script under test
require("repos-backup.inc.php");
// import testing framework, see http://www.lastcraft.com/simple_test.php
require("../lib/simpletest/setup.php");

class TestReposBackup extends UnitTestCase {

	function setUp() {
		
	}
	
	function testNothing() {
		
	}
	
	function testAnalyzeErrorMessage() {
		$err = array(
		"svnadmin: File not found: transaction '68-1', path 'test/trunk/testfile.txt",
		"'<<< Started new transaction, based on original revision 51
     * adding path : test/trunk/excelfile.xml ... done.

------- Committed revision 51 >>>"
		); // stderr is often returned above stdout
		$msg = analyzeBackupLoadError($err);
		$this->sendMessage($msg);
		$this->assertPattern('/backup integrity error/i', $msg);
		$this->assertPattern('/test\/trunk\/testfile.txt/', $msg);
		$this->assertPattern('/(revis|vers)ion 68/', $msg);
		$this->assertPattern('/loaded.*51/i', $msg);
	}
	
	function testAnalyzeErrorMessageNoError() {
		$out = '<<< Started new transaction, based on original revision 51
     * adding path : test/trunk/excelfile.xml ... done.

------- Committed revision 51 >>>

<<< Started new transaction, based on original revision 52
     * deleting path : test/trunk/excelfile.xml ... done.

------- Committed revision 52 >>>

<<< Started new transaction, based on original revision 53
     * adding path : test/trunk/folder1 ... done.

------- Committed revision 53 >>>

<<< Started new transaction, based on original revision 54
     * adding path : test/trunk/folder1/subfolder1 ... done.

------- Committed revision 54 >>>

<<< Started new transaction, based on original revision 55
     * deleting path : test/trunk/folder1 ... done.

------- Committed revision 55 >>>

<<< Started new transaction, based on original revision 56
     * adding path : test/trunk/folder1 ... done.

------- Committed revision 56 >>>

<<< Started new transaction, based on original revision 57
     * adding path : test/trunk/renamedfolder ...COPIED... done.
     * deleting path : test/trunk/folder1 ... done.

------- Committed revision 57 >>>

<<< Started new transaction, based on original revision 58
     * deleting path : test/trunk/renamedfolder ... done.';
		
		$msg = analyzeBackupLoadError(explode("\n", $out));
		$this->sendMessage($msg);
		
	}
	
	function testLoadTwoRevisions() {
		// exact copy paste from dumpfile producted by repos
		$dump = 'SVN-fs-dump-format-version: 2

UUID: 16b22533-16db-d043-87d6-d26b3346928b

Revision-number: 12
Prop-content-length: 100
Content-length: 100

K 7
svn:log
V 1
1
K 10
svn:author
V 5
admin
K 8
svn:date
V 27
2007-03-08T10:46:53.429124Z
PROPS-END

Node-path: backuptest1
Node-kind: dir
Node-action: add
Prop-content-length: 10
Content-length: 10

PROPS-END


SVN-fs-dump-format-version: 2

UUID: 16b22533-16db-d043-87d6-d26b3346928b

Revision-number: 13
Prop-content-length: 107
Content-length: 107

K 7
svn:log
V 8
delete 1
K 10
svn:author
V 5
admin
K 8
svn:date
V 27
2007-03-08T10:49:42.832715Z
PROPS-END

Node-path: backuptest1
Node-action: delete


';
		$dump = str_replace("\r\n", "\n", $dump); // dumpfile is unix newline on windows too
		$textMd5 = '723745f47720abc6f367bae6eb1e681a';
		$this->assertEqual($textMd5, md5($dump), "Verify that the test data matches the md5 from repos backup sample. %s");
		// write test data to temp file
		$tmp = System::getTempFile('backuptest');
		$f = gzopen($tmp, 'w');
		gzwrite($f, $dump);
		gzclose($f);
		// verify that this test data is the same that repos backup produces
		$gzipMd5 = 'e23041c33784e4de57ed4655f0d3ca57';
		$gzipMd5alt = '329cfa62d24eb514e82058c883607ec2'; // on the unix server. why different?
		$gzipMd5actual = md5_file($tmp);
		$this->assertTrue($gzipMd5 == $gzipMd5actual || $gzipMd5alt == $gzipMd5actual, "Verify test file integrity before running test. %s");
		$md5String = $gzipMd5actual.'  '.basename($tmp);
		$md5file = dirname($tmp).'/repos-backup.md5';
		System::createFileWithContents($md5file, $md5String);
		
		// along the lines of REPOS-7, the dumpfile could be split into one file per revision.
		// however, with the current concept of a size limit for backup archives, the only load jobs
		// that take a lot of time are those with one huge revision.
		$repo = System::getTempFolder('backuptest');
		exec('svnadmin create "'.$repo.'"');
		// run the test
		loadDumpfile($tmp, $repo);
		exec('svnadmin verify "'.$repo.'" 2>&1', $output, $result);
		$this->assertEqual(0, $result, "svnadmin verify on loaded contents should return 0. %1");
		$this->sendMessage($output);
		$this->assertTrue(strContains(implode("\n", $output), 'Verified revision 2'),
			"Should have found 2 loaded revisions. %s");
		
		// clean up
		System::deleteFolder($repo);
		System::deleteFile($tmp);
		System::deleteFile($md5file);
	}
	
}

testrun(new TestReposBackup());
?>
