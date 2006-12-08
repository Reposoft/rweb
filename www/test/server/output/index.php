<?php
/**
 * Tests the HTTP response handling.
 * 
 * @package test
 */

require('../../../conf/Report.class.php');

$report = new Report('Testing report output, one line per second');

$max = 120;
if (isset($_GET['max'])) {
	$max = $_GET['max'];
} else {
	$report->info('<p><a id="start" href="?max=10">Run for 10 seconds</a></p>');
	$report->info('<p><a id="start2" href="?max=120">Run for 2 minutes</a></p>');
	$report->display();
	exit;
}

$explicitFlush = false;

if (is_numeric($max) && $max > 0 && $max < 60*60) {
	$report->info("Will run for $max seconds. Reports should not do output buffering. If they do, this page will show only when everything is loaded.");
	$report->info("Note that set_time_limit has not been called, so depending on the server configuration the script might terminate prematurely.");
	if ($explicitFlush) $report->warn("Doing explicit flush in this page, so this test does not nessecarily reflect the application's behaviour.");
	$report->info("---- output starts now -----");
	if (ob_get_contents()) {
		$report->fail("Seem like output is buffered");
		ob_flush(); flush();
	}
} else {
	trigger_error("Invalid max value: $max");
}

for ($i = 1; $i <= $max; $i++) {
	$report->info("|$i|");
	if ($explicitFlush && ob_get_contents()) { ob_flush(); flush(); }
	sleep(1);
}
if (!ob_get_contents()) $report->ok("The buffer is empty. That's good.");
$report->info("---- output completed ----");
$report->display();
?>
 