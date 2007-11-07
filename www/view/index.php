<?php
require(dirname(dirname(__FILE__)) . '/conf/repos.properties.php');
require(dirname(dirname(__FILE__)) . '/conf/System.class.php');

define('VIEW_INTERVAL',2);
define('VIEW_TIMEOUT',ini_get('max_execution_time') - VIEW_INTERVAL);

header('Content-Type: text/html; charset=utf-8');

if(isset($_GET['result'])) {
	$resultFile = System::getApplicationTemp('pages') . $_GET['result'];
	if(!file_exists($resultFile)){
		if (isset($_GET['w'])) {
			$w = $_GET['w'];
			if ($w >= VIEW_TIMEOUT) {
				echo "Operation timed out";
				echo " $resultFile";
			} else {
				$waitUrl = getSelfUrl().'?'.str_replace('w='.$w,'w='.($w+VIEW_INTERVAL),getSelfQuery());
				header('Refresh: '.VIEW_INTERVAL.'; url='.$waitUrl);
				echo '<html><body><h4>Processing...'.str_repeat('.',$w).'</h4></body></html>';
			}
		} else {
			echo "This page does not exist. <br />";
			echo "Result pages are temporary. $resultFile has expired.";
		}
		exit;
	}
	$handle = fopen($resultFile, "r");
	fpassthru($handle);
	fclose($handle);
	exit;
} else {
	trigger_error('Result ID not set. Can not view page.', E_USER_ERROR);
}
