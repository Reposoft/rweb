<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>repos.se php unittests</title>
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
$testfiles = array(
'conf/repos.properties.test.php',
'conf/Presentation.test.php',
'conf/Report.test.php',
'account/login.test.php',
'edit/edit.test.php',
'open/start/RepositoryTree.test.php',
'plugins/validation/Validation.test.php'
);

function printTestSuite($testfiles) {
	echo("<tr><td><b>repos.se PHP Test Suite</b></td></tr>\n");
	foreach ($testfiles as $file) {
		echo("<tr><td><a href=\"?file=$file\">$file</a></td></tr>\n");
	}
}

function printTestCase($file) {
?>
<tr><td rowspan="1" colspan="3"><?php echo($file); ?></td></tr>
</thead><tbody>
<tr>
	<td>open</td>
	<td><?php echo('../../'.$file); ?></td>
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
<?php
}

if (isset($_GET['file'])) {
	$file = $_GET['file'];
	if (!in_array($file, $testfiles)) {
		trigger_error("$file is not a known testcase");
		exit;
	}
	printTestCase($file);
} else {
	printTestSuite($testfiles);
}

?>
	</tbody>
</table>
<?php
if (!isset($_GET['file'])) {
	echo('<p><a class="action" id="back" href="./" target="_top">&lt back</a></p>');
}
?>
</body>
</html>
