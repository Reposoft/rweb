<?php
// get the details of a file and return as XML, JSON or HTML
require( dirname(dirname(dirname(__FILE__)))."/account/login.inc.php" );

// format could be specified as paramter

$url = getTargetUrl();
if (!$url) trigger_error("'target' must be set");

// XML format
header('Content-type: text/xml; encoding=utf-8');
$cmd = 'list --xml '.escapeArgument($url);
$return = login_svnPassthru($cmd);
if ($return) {
	login_handleSvnError($cmd, $return);
}

?>