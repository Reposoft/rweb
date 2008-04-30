<?php
require(dirname(dirname(__FILE__)) . '/conf/repos.properties.php');
require(dirname(dirname(__FILE__)) . '/conf/System.class.php');

define('VIEW_INTERVAL',1);
define('VIEW_TIMEOUT',ini_get('max_execution_time') - 1 - VIEW_INTERVAL);

header('Content-Type: text/html; charset=utf-8');

// TODO Last-modified header for ajax calls?

if(isset($_GET['result'])) {
	$resultFile = System::getApplicationTemp('pages') . $_GET['result'];
	if(!file_exists($resultFile)){
		// Presentation not loaded so error message would look ugly
		// trigger_error("Page does not exist. Result page ".basename($resultFile)." has expired.", E_USER_NOTICE);
		echo "This page does not exist. <br />";
		echo "Result page ".basename($resultFile)." has expired.";
		exit;
	}
	// original page should have done touch()
	$background = !filesize($resultFile);
	if ($background) {
		waitPage();
		clearstatcache();
	}
	// loop and wait until page contents have been written by background process
	$w = 0;
	while ($background && !filesize($resultFile)) {
		if ($w++ >= VIEW_TIMEOUT) {
			echo '<p class="error">Timeout</p>';
			exit;
		}
		echo '<span class="wait">.</span>';
		@ob_flush();flush(); // according to the docs this is still not 100% reliable for every possible php configuration
		sleep(VIEW_INTERVAL);
		clearstatcache();
	}
	// read page
	$handle = fopen($resultFile, "r");
	// cut contents from background page for incremental display
	if ($background) {
		echo '<span class="wait"> completed.</span>';
		while (!feof($handle)) {
			$b = fread($handle, 1024);
			$p = strpos($b, '<body');
			if ($p !== false) {
				echo substr($b, 1+strpos($b,'>',$p));
				break;
			}
		}
	}
	// show the rest of the page
	fpassthru($handle);
	fclose($handle);
} else {
	trigger_error('Result ID not set. Can not view page.', E_USER_ERROR);
}

// header for page displayed when waiting
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
