<?php
define('PARENT_DIR',substr(dirname(__FILE__), 0, strrpos(rtrim(strtr(dirname(__FILE__),'\\','/'),'/'),'/')));
require( PARENT_DIR."/login.inc.php" );

// get file to open

// #debug#
$path = $_GET['path'];
$file = $_GET['file'];
// #######

$url = getTargetUrl();
$type = substr($url, strrpos($url, '.') + 1);

// iCalendar files
if ($type=='ics') {
	/* PHP iCalendsr config must be changed to: 
		$allow_webcals  = 'yes';
		$allow_login = 'yes';
	*/
	header("Location: ../phpicalendar/?cal=$url");
}

echo "Support for file type &quot;$type&quot; not added yet.";
?>