<?php
/**
 * Generates a test suite with n runs of a given test case.
 *
 * @package test
 */
define('COOKIE_TESTCASE', 'suite_testcase');
define('COOKIE_TESTCASE_N', 'suite_n');

if (isset($_GET['testcase'])) {
	$testcase = urldecode($_GET['testcase']);
	$n = 100;
	if (isset($_GET['n'])) $n = $_GET['n'];	
	setcookie(COOKIE_TESTCASE, $testcase);
	setcookie(COOKIE_TESTCASE_N, $n);	
	header('Location: /repos/test/selenium/TestRunner.html?test=..%2FPerformance.php&auto=true&resultsUrl=..%2Fpostresults%2F');
	exit;
} else {
	if (!isset($_COOKIE[COOKIE_TESTCASE])) trigger_error("'testcase' parameter required");
	if (!isset($_COOKIE[COOKIE_TESTCASE_N])) trigger_error("'repos_test_n' cookie required with testcase cookie");
	$testcase = $_COOKIE[COOKIE_TESTCASE];
	$n = $_COOKIE[COOKIE_TESTCASE_N];
}

$tests = array();

for ($i=0; $i<$n; $i++) {
	$tests[$i] = $testcase;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>repos.se selenium testsuite</title>
<style>
#suiteTable td {
	margin: 0px;
	padding: 0px;
}
</style>
<link href="../style/global.css" rel="stylesheet" type="text/css" />
<link href="../style/docs.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="suite.js"></script>
</head>
<body>
<table id="suiteTable" class="rows" width="100%">
	<tbody>
	<tr><th>repos performance test</th></tr>
	<tr>
	  <td><a href="reset/TestReset.html">reset testrepo</a></td>
	</tr>
<?php foreach ($tests as $case) {
	echo("<tr>
	  <td><a href=\"$case\">$case</a></td>
	</tr>
	");
} ?>
	</tbody>
</table>
<p><a class="action" id="back" href="./" target="_top">&lt; back</a></p>
</body>
</html>