<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require_once( upOne(dirname(__FILE__)) . "/conf/repos.properties.php" );
require_once( upOne(dirname(__FILE__)) . "/login.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');

$url = getTargetUrl();
$revfrom = $_GET['revfrom'];
$revto = $_GET['revto'];
if(empty($rev)) {
	echo "Argument error: 'rev' not specified.";
	exit;
}
$revisions = ' -r '.$revfrom.':'.$revto;

$cmd = 'diff' . $revisions . " $url";

// passthrough with stylesheet
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
echo "<!-- SVN cat for .$url. -->\n";
echo '<cat repo="'.getRepositoryUrl().'" path="'.$_GET['path'].'" file="'.$_GET['file'].' rev="'.$rev.'">' . "\n";
echo '<![CDATA['."\n";
svnPassthru($cmd);
echo ']]>'."\n";
echo '</diff>';
?>