<?php
require(dirname(dirname(__FILE__)).'/conf/Presentation.class.php');
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

$revisionRule = new RevisionRule();

$rev = $revisionRule->getValue();

$file = null;
if ($rev) {
	$file = new SvnOpenFile(getTarget(), $rev);		
} else {
	$file = new SvnOpenFile(getTarget());
}

$p = new Presentation();
$p->assign_by_ref('file', $file);
$p->display();

// go directly to the resource in repository // header("Location: ".getConfig('repo_root')."$url");

?>