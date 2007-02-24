<?php

require('json.php');

$report = new Report('Install Smarty');
if (class_exists('Services_JSON')) {
	$report->ok('Services_JSON is installed, done.');
} else {
	$report->fail('Serivces_JSON class does not exist');
}
$report->display();

?>
