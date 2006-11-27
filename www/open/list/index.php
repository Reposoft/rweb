<?php
// get the details of a file and return as XML, JSON or HTML
require( dirname(dirname(dirname(__FILE__)))."/account/login.inc.php" );

// format could be specified as paramter

$url = getTargetUrl();
if (!$url) trigger_error("'target' must be set");

// XML format
$cmd = 'list --xml '.escapeArgument($url);
$list = login_svnRun($cmd);
if (($result=array_pop($list))!=0) {
	login_handleSvnError($cmd, $result);
}

header('Content-Type: text/xml; charset=utf-8');
$size = 0;
for ($i=0; $i<count($list); $i++) {
	$size += strlen($list[$i]);
}
header('Content-Length: '.$size);

for ($i=0; $i<count($list); $i++) {
	echo($list[$i]);
}

?>