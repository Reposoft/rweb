<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require_once( upOne(dirname(__FILE__)) . "/conf/repos.properties.php" );
require_once( upOne(dirname(__FILE__)) . "/login.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');
$justOpen = isset($_GET['open']);

$url = getTargetUrl();
$rev = $_GET['rev'];
if(empty($rev)) {
	echo "Argument error: 'rev' not specified.";
	exit;
}
$revisions = ' -r '.$rev;
$cmd = 'cat' . $revisions . ' "'.$url.'"';

// passthrough with stylesheet
if ($justOpen) {
	// header('Content-type: application/pdf');
	// It will be called downloaded.pdf
	header('Content-Disposition: attachment; filename="'.basename(getTarget()).'"');
} else {
	header('Content-type: text/xml');
	echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
	echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
	echo "<!-- SVN cat for .$url. -->\n";
	echo '<cat repo="'.getRepositoryUrl().'" target="'.getTarget().'" rev="'.$rev.'"><plaintext>' . "\n";
	echo "<![CDATA[\n";
}
svnPassthru($cmd);
if(!$justOpen) {
	echo "\n]]>\n";
	echo '</plaintext></cat>';
}
?>