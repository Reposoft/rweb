<?php
/**
 * Returns 'svn list' for webservice calls,
 * for example to the details plugin
 */

// get the details of a file or folder and return as XML
require( dirname(dirname(__FILE__))."/SvnOpen.class.php" );

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