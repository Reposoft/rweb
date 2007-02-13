<?php
require(dirname(dirname(__FILE__)) . '/conf/repos.properties.php');
require(dirname(dirname(__FILE__)) . '/conf/System.class.php');

if(isset($_GET['result'])) {
	$resultFile = System::getApplicationTemp('pages') . $_GET['result'];
	if(!file_exists($resultFile)){
		echo "This page does not exist. <br />";
		echo "Result pages are temporary. $resultFile has expired.";
		exit;
	}
	$handle = fopen($resultFile, "r");
	fpassthru($handle);
	fclose($handle);
	exit;
} else {
	trigger_error('Result ID not set. Can not view page.', E_USER_ERROR);
}
