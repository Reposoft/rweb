<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require_once( upOne(dirname(__FILE__)) . "/conf/authentication.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');

$url = getTargetUrl();
//$fromrev = $_GET['fromrev'];
//$torev = $_GET['torev'];
$user = getReposUser();
$pass = getReposPass();
$auth = " --username=$user --password=$pass --no-auth-cache";
$options = " -v --xml";
$options .= " --incremental"; // to avoid xml declaration 
//$revisions = " -r HEAD";

$cmd = getCommand('svn') . $auth . $options . $revisions . " log $url";

// passthrough with stylesheet
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
echo "<!-- SVN log for .$url. -->\n";
echo '<log repo="'.getRepositoryUrl().'" path="'.$_GET['path'].'">' . "\n";
passthru($cmd);
echo '</log>';
?>
