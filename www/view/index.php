<?php
require(dirname(dirname(__FILE__)) . '/conf/repos.properties.php');

if(isset($_GET['result'])) {
	$resultFile = getTempDir('pages') . $_GET['result'];
	$handle = fopen($resultFile, "r");
	fpassthru($handle);
	fclose($handle);
	exit;
} else {
	trigger_error('Result ID not set. Can not view page.', E_USER_ERROR);
}
