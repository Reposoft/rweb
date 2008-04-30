<?php
require(dirname(dirname(__FILE__)) . '/conf/repos.properties.php');
require(dirname(dirname(__FILE__)) . '/conf/System.class.php');

define('VIEW_INTERVAL',1);
define('VIEW_TIMEOUT',ini_get('max_execution_time') - VIEW_INTERVAL);

header('Content-Type: text/html; charset=utf-8');

// TODO Last-modified header for ajax calls?

if(isset($_GET['result'])) {
	$resultFile = System::getApplicationTemp('pages') . $_GET['result'];
	if(!file_exists($resultFile)){
		echo "This page does not exist. <br />";
		echo "Result pages are temporary. $resultFile has expired.";
	}
	// original page should have done touch()
	if (!filesize($resultFile)) {
		waitPage();
		clearstatcache();
	} else {
		echo "mupp"; exit;
	}
	while (!filesize($resultFile)) {
		echo '<span class="wait">.</span>';
		@ob_flush();flush(); // according to the docs this is still not 100% reliable
		sleep(VIEW_INTERVAL);
		clearstatcache();
	} // TODO timeout
	$handle = fopen($resultFile, "r");
	fpassthru($handle);
	fclose($handle);
} else {
	trigger_error('Result ID not set. Can not view page.', E_USER_ERROR);
}

function waitPage() {
	$w = getWebapp();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>repos operation in progress</title>
<meta name="repos-service" content="result/" />
<link href="<?php echo $w; ?>style/global.css" rel="stylesheet" type="text/css"></link>
<script type="text/javascript" src="<?php echo $w; ?>scripts/head.js"></script>
</head>
<body>
<span class="wait">Processing...</span><?php // no newline
	@ob_flush();flush(); 
}
