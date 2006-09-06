<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require_once( upOne(dirname(__FILE__)) . "/account/login.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');

$url = urlEncodeNames(getTargetUrl());
if (!getPath()) {
	echo("Log withoug the 'path' parameter has not been implemented yet");exit;
}

$cmd = "log -v --xml --incremental ".$url;

// start output

// Set HTTP output character encoding to SJIS
mb_http_output('UTF-8');
// Start buffering and specify "mb_output_handler" as callback function
ob_start('mb_output_handler');

// passthrough with stylesheet
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
echo "<!-- SVN log for $url -->\n";
echo '<log repo="'.getRepositoryUrl().'" path="'.getPath().'">' . "\n";
$returnvalue = login_svnPassthru($cmd);
if ($returnvalue) login_handleSvnError($cmd, $returnvalue);
echo '</log>';
?>