<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require_once( upOne(dirname(__FILE__)) . "/conf/repos.properties.php" );
require_once( upOne(dirname(__FILE__)) . "/login.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');

$url = getTargetUrl();

$cmd = getSvnCommand()."log $url";

// passthrough with stylesheet
echo "$cmd";
passthru($cmd);
echo '</log>';
?>