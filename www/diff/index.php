<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require_once( upOne(dirname(__FILE__)) . "/conf/authentication.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');

$url = getTargetUrl();
$revfrom = $_GET['revfrom'];
$revto = $_GET['revto'];
if(empty($revfrom) || empty($revto)) {
	echo "Argument error: 'revfrom' and 'revto' not specified.";
	exit;
}
//$fromrev = $_GET['fromrev'];
//$torev = $_GET['torev'];
$user = getReposUser();
$pass = getReposPass();
$auth = " --username=$user --password=$pass --no-auth-cache";
$options = " --non-interactive";
$revisions = ' -r '.$revfrom.':'.$revto;

$cmd = getCommand('svn') . $auth . $options . $revisions . " diff $url";

// passthrough with stylesheet
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
echo "<!-- SVN diff for .$url. -->\n";
echo '<diff repo="'.getRepositoryUrl().'" path="'.$_GET['path'].'" revfrom="'.$revfrom.'" revto="'.$revto.'">' . "\n";
echo '<![CDATA['."\n";
passthru($cmd);
echo ']]>'."\n";
echo '</diff>';
?>
