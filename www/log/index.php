<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require_once( upOne(dirname(__FILE__)) . "/conf/repos.properties.php" );
require_once( upOne(dirname(__FILE__)) . "/login.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');

$url = getTargetUrl();
$cmd = getSvnCommand()." log --xml --incremental $url";

// passthrough with stylesheet
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
echo "<!-- SVN log for .$url. -->\n";
echo '<log repo="'.getRepositoryUrl().'" path="'.$_GET['path'].'">' . "\n";
passthru($cmd);
echo '</log>';
?>