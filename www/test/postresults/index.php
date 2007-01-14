<?php
/**
 * (c) repos.se
 * Results URL for selenium test runner.
 * 
 * @package test
 */
// use preconfigured smarty
require('../../lib/smarty/smarty.inc.php');
// settings
define('COOKIE_TESTCASE', 'suite_testcase');
define('COOKIE_TESTCASE_N', 'suite_n');

$runid = date('Y-m-d_His');
$logpath = dirname(dirname(dirname(dirname(__FILE__)))).'/testresults/';
$logfile = $logpath.$runid.'.html';

// test suite
if (!isset($_SERVER['HTTP_REFERER'])) { echo "No referer, abort."; exit; }
$url = $_SERVER['HTTP_REFERER'];

// get host and uri to the test home, and name of the testsuite
$testsuite_pattern ='/^([^?]+test\/)(?:selenium\/TestRunner\.html)\?.*test=[\.\/]*(?:%2F)*([^&]+)/';
if (!preg_match($testsuite_pattern, $url, $matches)) {
	echo("Could not find test=TestSuiteName in test runner referrer $suite"); exit;
}
$testurl = $matches[1];
$suite = $matches[2];

// dynamic test suites: take name from cookie
if (strtolower($suite) == strtolower('Performance.php')) {
	if (!isset($_COOKIE[COOKIE_TESTCASE])) {
		echo("Dynamic test suite, but could not find cookie ".COOKIE_TESTCASE);
		exit;
	}
	$url = $testurl.$suite;
	$suite = $_COOKIE[COOKIE_TESTCASE];
	$url .= '?testcase='.$suite;
	if (isset($_COOKIE[COOKIE_TESTCASE_N])) {
		$suite .= '-x'.$_COOKIE[COOKIE_TESTCASE_N]; 
		$url .= '&n='.$_COOKIE[COOKIE_TESTCASE_N];
	}
}

// make a valid html id of the suite url
$suiteid = strtr($suite, '%/()@', '_____');

// client information
$client = array(
'browser' => $_SERVER["HTTP_USER_AGENT"],
'address' => $_SERVER["REMOTE_ADDR"],
'local' => isset($_SERVER["IS_LOCAL_CLIENT"]) ? "yes" : "no", // defined by server config
'admin' => isset($_SERVER["IS_ADMIN_CLIENT"]) ? "yes" : "no" // defined by server config
);
if (isset($_SERVER["REMOTE_USER"])) {
	$client['remoteuser'] = $_SERVER["REMOTE_USER"];
} else {
	$client['remoteuser'] = '(not available)';
}
if (isset($_SERVER["PHP_AUTH_USER"])) {
	$client['reposuser'] = $_SERVER["PHP_AUTH_USER"];
} else {
	$client['reposuser'] = '(not available)';
}

// server variables
$server = array(
'ServerName' => $_SERVER['SERVER_NAME'],
'hostname' => $_SERVER["HTTP_HOST"],
'address' => $_SERVER["SERVER_ADDR"],
'https' => isset($_SERVER["HTTPS"]) ? "yes" : "no",
'phpversion' => phpversion()
);

// --- create Smarty page ---
$s = smarty_getInstance();

// test suite info
$s->assign('runid', $runid);
$s->assign('suite', $suite);
$s->assign('suiteid', $suiteid);
$s->assign('url', $url);
// arrays
$s->assign('server', $server);
$s->assign('client', $client);

// update suite table with links to anchors
$suitetable = $_REQUEST['suite'];
$testpattern = '/<td>.*<a.*href="([^"]{6,}")/'; // assume max 999 tables 
for ($i = 1; $i<=$_REQUEST['numTestTotal']; $i++) {
	if (preg_match($testpattern, $suitetable, $matches)) {
		$suitetable = str_replace($matches[1], "#t$i\" title=\"$matches[1]}", $suitetable);
	}
}
// delete everything after end of table
$end = strpos($suitetable, '</table>');
if ($end > 0) $suitetable = substr($suitetable, 0, $end+8);
// update the selenium suite
$_REQUEST['suite'] = $suitetable;

// posted data from selenium
$s->assign('se', $_REQUEST);
// tables are named testTable_1 etc and need to be extracted to loopable array here
$tables = array();
$pattern = '/testTable_(\d)+/';
foreach ($_REQUEST as $k => $v) {
	if (preg_match($pattern, $k, $matches)) {
		$tables[$matches[1]] = $v;
	}
}
$s->assign_by_ref('tables', $tables);

$page = $s->fetch(dirname(__FILE__).'/testrun.html');

// ---- write results ----
if (touch($logfile)) {
	$h = fopen($logfile,'w');
	fwrite($h, $page);
	fclose($h);
} else {
	$email = $_SERVER["SERVER_ADMIN"];
	if (strpos($email, '@')===false) $email = 'admin@repos.se';
	mail($email,
		"Test results $runid",
		"You get these mails because test results could not be written to $logfile.\n".
		"Please make sure that the 'testresults' folder exists and is writable.\n\n".
		$page);
}

// --- create xml with summary of each run ---
// create an empty file if it does not exist
$datafile = $logpath.'testruns.xml';
$xsl = 'testruns.xsl';
if (!file_exists($datafile)) {
	if (!touch($datafile)) trigger_error("Could not create datafile", E_USER_ERROR);
	$h = fopen($datafile, 'w');
	fwrite($h, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
	fwrite($h, '<?xml-stylesheet type="text/xsl" href="'.$xsl.'"?>'."\n");
	fwrite($h, "<testruns>\n");
	fwrite($h, "</testruns>\n");
	fclose($h);
}
if (!file_exists($logpath.$xsl) && file_exists($xsl)) {
	copy($xsl, $logpath.$xsl);
}

// xml result
$entries = array(
'tests' => 'numTestTotal',
'time' => 'totalTime',
'result' => 'result',
'passed' => 'numTestPasses',
'failures' => 'numTestFailures'
);
// TODO add browser, os, client info
$suitestart = '<suite id="'.$suiteid.'">';
$newrun = '<run id="'.$runid.'">';
foreach ($entries as $k => $v) $newrun .= '<'.$k.'>'.$_REQUEST[$v].'</'.$k.'>';
$newrun .= "</run>\n";

$h = fopen($datafile, 'r');
$w = fopen($datafile.'.tmp', 'w');
$written = false;
while (!feof($h)) {
   $line = fgets($h, 4096);
	if (trim($line)==$suitestart) {
   	fwrite($w, $line);
   	fwrite($w, $newrun);	
   	$written = true;
   } else if (!$written && trim($line)=='</testruns>') {
   	fwrite($w, $suitestart."\n");
   	fwrite($w, $newrun);
   	fwrite($w, "</suite>\n");
   	fwrite($w, $line);
   } else {
   	fwrite($w, $line);
   }
}
fclose($h);
fclose($w);
unlink($datafile);
rename($datafile.'.tmp', $datafile);

echo($page);

?>
