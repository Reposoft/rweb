<?php
// get the properties of a file or folder as JSON 
require( dirname(dirname(dirname(__FILE__)))."/account/login.inc.php" );
require( dirname(dirname(dirname(__FILE__)))."/lib/json/json.php" );

$url = getTargetUrl();
if (!$url) trigger_error("'target' must be set");

header('Content-type: text/plain');
$cmd = new SvnOpen('proplist');
$cmd->addArgUrl($url);
if ($cmd->exec()) {
	trigger_error(implode("\n",$cmd->getOutput()), E_USER_ERROR);	
}

// to get all the properties of a specific type for a tree,
// use propget -R [propertyname] path
// but that's probably a different service

?>