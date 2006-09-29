<?php

require_once(dirname(__FILE__).'/simpletest/unit_tester.php');
//require_once(dirname(__FILE__).'/simpletest/reporter.php');
// using our own custom HtmlReporter, TestReporter and SelectiveReporter
require_once(dirname(__FILE__).'/reporter.php');

function testrun($testcase) {
	$testcase->run(new HtmlReporter());
}

?>