<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>repos.se javascript unittests</title>
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
'scripts/prepare/index.xml',
);

function printTestSuite($testfiles) {
	echo("<tr><td><b>repos.se Javascript Tests</b></td></tr>\n");
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
</body>
</html>
