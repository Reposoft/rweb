<?php
// get the properties of a file or folder as JSON 
require( dirname(dirname(dirname(__FILE__)))."/account/login.inc.php" );
require( dirname(dirname(dirname(__FILE__)))."/lib/json/json.php" );

$url = getTargetUrl();
if (!$url) trigger_error("'target' must be set");

// XML format
header('Content-type: text/plain');
$cmd = 'proplist '.escapeArgument($url);
$proplist = login_svnRun($cmd);
if (array_pop($proplist) != 0) {
	login_handleSvnError($cmd, $return);
}

// to get all the properties of a specific type for a tree,
// use propget -R [propertyname] path
// but that's probably a different service

?>