<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require( upOne(dirname(__FILE__)) . "/conf/repos.properties.php" );
require( upOne(dirname(__FILE__)) . "/conf/authentication.inc.php" );

$url = $_GET['url'];
//$fromrev = $_GET['fromrev'];
//$torev = $_GET['torev'];
$user = getReposUser();
$pass = getReposPass();
$auth = " --username=$user --password=$pass";
$options = " -v --xml";
//$revisions = " -r HEAD";

$cmd = getCommand('svn') . $auth . $options . $revisions . " log $url";

// passthrough with stylesheet
header('Content-type: text/xml');
passthru($cmd);

?>