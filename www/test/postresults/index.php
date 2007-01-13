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
$logfile = $logpath.$runid;

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
	$suite = $_COOKIE[COOKIE_TESTCASE];
	$url = $testurl.$suite.'?testcase='.$suite;
	if (isset($_COOKIE[COOKIE_TESTCASE_N])) {
		$suite .= '_x'.$_COOKIE[COOKIE_TESTCASE_N]; 
		$url .= '&n='.$_COOKIE[COOKIE_TESTCASE_N];
	}
}

// make a valid html id
$suiteid = strtr($suite, '%/()@', '_____');

// client information
$client = array(
'browser' => $_SERVER["HTTP_USER_AGENT"],
'address' => $_SERVER["REMOTE_ADDR"],
'local' => isset($_SERVER["IS_LOCAL_CLIENT"]), // defined by server config
'admin' => isset($_SERVER["IS_ADMIN_CLIENT"]) // defined by server config
);

if (isset($_SERVER["REMOTE_USER"])) {
	$client['remoteuser'] = $_SERVER["REMOTE_USER"];
} else {
	$client['remoteuser'] = '';
}
if (isset($_SERVER["PHP_AUTH_USER"])) {
	$client['reposuser'] = $_SERVER["PHP_AUTH_USER"];
} else {
	$client['reposuser'] = '';
}

// server variables
$server = array(
'hostname' => $_SERVER["HTTP_HOST"],
'address' => $_SERVER["SERVER_ADDR"],
'https' => isset($_SERVER["HTTPS"]),
'phpversion' => phpversion()
);

$result = $suiteid.":\n";
foreach($server as $key => $value) {
	$result .= '[server:'.$key.']: '.$value."\n";
}
foreach($client as $key => $value) {
	$result .= '[client:'.$key.']: '.$value."\n";
}
foreach($_REQUEST as $key => $value) {
	$result .= '['.$key.']: '.$value."\n";
}

// --- create Smarty page ---
$s = smarty_getInstance();

$s->assign('data', $result);
$s->assign('runid', $runid);
$s->assign('suite', $suite);
$s->assign('suiteid', $suiteid);
$s->assign('url', $url);
// arrays
$s->assign('server', $server);

$s->display(dirname(__FILE__).'/testrun.html');

// ---- write results ----
if (touch($logfile)) {
	$h = fopen($logfile,'w');
	fwrite($h, $result);
	fclose($h);
} else {
	$email = $_SERVER["SERVER_ADMIN"];
	if (strpos($email, '@')===false) $email = 'admin@repos.se';
	mail($email,
		"Test results $run_id",
		"You get these mails because test results could not be written to $logfile\n\n".
		$result);
}

//echo($result);

?>
