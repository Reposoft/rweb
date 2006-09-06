<?php
require( dirname(dirname(__FILE__))."/account/login.inc.php" );

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
        header("Location: ".getConfig('repos_web')."/phpicalendar/");
} else {
        header("Location: ".getConfig('repo_root')."$url");
}
?>