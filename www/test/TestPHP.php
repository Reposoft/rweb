<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>repos: php unittests</title>
<link href="../style/global.css" rel="stylesheet" type="text/css" />
<link href="../style/docs.css" rel="stylesheet" type="text/css" />
<style>
#suiteTable td {
	margin: 0px;
	padding: 0px;
}
</style>
</head>

<body>
<table id="suiteTable" class="rows" width="100%" border="0">
	<tbody>
<?php
/**
 * The test suite for php unit tests.
 * 
 * @package test
 */

if (!defined('TEST_INTEGRATION')) define('TEST_INTEGRATION', true);

$testfiles = array(
'conf/php/TestServerSettings.php',
'conf/System.test.php',
'conf/repos.properties.test.php',
'conf/Presentation.test.php',
'conf/Report.test.php',
'conf/Command.test.php',
'account/login.test.php',
'account/login.testi.php',
'account/RepositoryTree.test.php',
'open/SvnOpen.test.php',
'open/SvnOpenFile.test.php',
'open/SvnOpenFile.testi.php',
'open/ServiceRequest.test.php',
'open/ServiceRequest.testi.php',
'edit/SvnEdit.test.php',
'edit/PresentEdit.test.php',
'edit/merge/conflicthandler.test.excel.php',
'edit/merge/xmlConflictHandler.test.php',
'plugins/validation/Validation.test.php',
'plugins/edit/edit.test.php',
'edit/upload/mimetype.test.php',
'edit/upload/filewrite.test.php',
'admin/admin.test.php',
'admin/repos-backup.test.php'
);

if (!TEST_INTEGRATION) {
	$testfiles = array_filter($testfiles, 'isNotIntegrationTest');
}

function isNotIntegrationTest($testName) {
	return !isIntegrationTest($testName);
}

function isIntegrationTest($testName) {
	return strpos($testName, '.testi.');
}

function printTestSuite($testfiles) {
	echo("<tr><td><b>repos.se PHP Test Suite</b></td></tr>\n");
	foreach ($testfiles as $file) {
		$name = substr($file, strpos($file, '/')+1);
		echo("<tr><td><a href=\"?file=$file\">$name</a></td></tr>\n");
	}
	echo ("</tbody></table>\n");
	echo('<p><a class="action" id="back" href="./" target="_top" accesskey="b">&lt <u>b</u>ack</a></p>');
}

function printTestCase($file) {
	$url = '/repos/'.$file;
?>
<tr><td rowspan="1" colspan="3"><?php echo($file); ?></td></tr>
</thead><tbody>
<tr>
	<td>open</td>
	<td><?php echo($url); ?></td>
	<td></td>
</tr>
<tr>
	<td>assertTextPresent</td>
	<td>0 fails</td>
	<td></td>
</tr>
<tr>
	<td>assertTextPresent</td>
	<td>0 exceptions</td>
	<td></td>
</tr>
</tbody>
</table>
<p><a class="action" id="open" href="<?php echo($url); ?>" target="_blank" accesskey="w">open in new <u>w</u>indow</a></p>
<p><small>Generally tests need PHP running in webserver (to get a URL and server variables).
<br />Integration tests require the /testrepo repository setup.</small></p>
<?php
}

if (isset($_GET['file'])) {
	$file = $_GET['file'];
	$file = str_replace('?thisIsChrome=false','',$file); // ignore selenium 0.8.2 stuff
	if (!in_array($file, $testfiles)) {
		trigger_error("$file is not a known testcase", E_USER_ERROR);
	}
	printTestCase($file);
} else {
	printTestSuite($testfiles);
}

?>
</body>
</html>
