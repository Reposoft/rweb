<?php
define('PARENT_DIR',substr(dirname(__FILE__), 0, strrpos(rtrim(strtr(dirname(__FILE__),'\\','/'),'/'),'/')));
require( PARENT_DIR."/account/login.inc.php" );

// get file to open

// #debug#
$path = $_GET['path'];
$file = $_GET['file'];
// #######

$url = getTargetUrl();
$type = substr($url, strrpos($url, '.') + 1);

// iCalendar files
if ($type=='ics') {
		setcookie("repos-calendar", $url, time()+3600, '/');
        header("Location: ../phpicalendar/");
} else {
        header("Location: $url");
}
?>