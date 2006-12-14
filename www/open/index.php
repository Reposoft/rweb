<?php
require( dirname(__FILE__)."/SvnOpenFile.class.php" );

// get file to open

$url = getTargetUrl();
$type = substr($url, strrpos($url, '.') + 1);

// iCalendar files
if ($type=='ics') {
		setcookie("repos-calendar", $url, time()+3600, '/');
	header("Location: ".getWebapp()."tools/calendar/");
	exit;
}

new RevisionRule();

Validation::expect('target', 'name', 'message');
if (isset($_GET['rev'])) {
	$rev = $_GET['rev'];
		
}

$file = new SvnOpenFile(getTarget());



// go directly to the resource in repository // header("Location: ".getConfig('repo_root')."$url");

?>