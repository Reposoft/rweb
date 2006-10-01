<?php

// allow other scripts to detect that they are running from a test case
define('TEST',$_SERVER['SCRIPT_FILENAME']);

require_once(dirname(__FILE__).'/simpletest/unit_tester.php');
//require_once(dirname(__FILE__).'/simpletest/reporter.php');
// using our own custom HtmlReporter, TestReporter and SelectiveReporter
require_once(dirname(__FILE__).'/reporter.php');

$reporter = new HtmlReporter();
function testrun(&$testcase) {
	global $reporter;
	$testcase->run($reporter);
}

if (function_exists('reportErrorToUser')) {
	trigger_error("Could not register custom error handling because the function 'reportErrorToUser' is already defined");
} else {
function reportErrorToUser($level, $message, $trace) {
	global $reporter;	
	if (!isset($reporter->report)) $reporter->paintHeader('errors before test start'); // not a good solution because simpletest also prints a header
	if ($level == E_USER_ERROR) {
		$reporter->paintError("Error:   ".$message);
	} else if ($level == E_USER_WARNING) {
		$reporter->paintError("Warning: ".$message);
	} else if ($level == E_USER_NOTICE) {
		$reporter->paintError("Notice:  ".$message);
	} else {
		if ($level==2048) { // E_STRICT not defined in PHP 4
			$t = explode("\n",$trace);
			if (strContains($t[1], 'simpletest')) return; // simpletest has many code check errors
		  	$reporter->paintMessage("PHP code check warning: ".$message);  		
		  	$reporter->paintFormattedMessage("$message\n$trace");
		} else {
		   	$reporter->paintMessage("Error of unknown type: ".$message);
		   	$reporter->paintFormattedMessage("$message\n$trace");
		}
	}
}
}

?>
