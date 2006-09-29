<?php

require_once(dirname(dirname(dirname(__FILE__))) . "/account/login.inc.php" );

define('STYLESHEET','../../view/log.xsl');

$url = getTargetUrl();
if (isFile($url)) {
	trigger_error("History for a single file has not been implemented yet");
	exit;
}

$cmd = 'log -v --xml --incremental '.escapeArgument($url); // escapeArgument not needed, because it is URLencoded 

// start output

// Set HTTP output character encoding to SJIS
//should be configured in server //mb_http_output('UTF-8');

// passthrough with stylesheet
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
echo "<!-- SVN log for $url -->\n";
echo '<log repo="'.getRepositoryUrl().'" path="'.getTarget().'">' . "\n";
$returnvalue = login_svnPassthru($cmd);
if ($returnvalue) login_handleSvnError($cmd, $returnvalue);
echo '</log>';
?>