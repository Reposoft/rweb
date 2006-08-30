<?php
// TODO: Cat is no good as XML data. Could always contain illegal chars. Just make it plain html instead.
require_once( dirname(dirname(__FILE__)) . "/account/login.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');

$url = urlEncodeNames(getTargetUrl());
if(!isset($_GET['rev'])) {
	trigger_error("Error: parameter 'rev' not specified.");
	exit;
}
$rev = $_GET['rev'];

// passthrough with stylesheet
if (isset($_GET['open'])) {
	// header('Content-type: application/pdf');
	// It will be called downloaded.pdf
	header('Content-Disposition: attachment; filename="'.basename(getTarget()).'"');
} else if (isset($_GET['display'])) {
	$revisions = ' -r '.$rev;
	$cmd = 'cat' . $revisions . ' "'.$url.'"';
	$returnvalue = login_svnPassthru($cmd);
	if ($returnvalue) {
		trigger_error("Error, code $returnvalue: could not complete operation \n$cmd");
	}
} else {
	$displayUrl = str_replace('&', '&amp;', repos_getSelfUrl().'?'.repos_getSelfQuery().'&display');
	header('Content-type: text/xml');
	echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
	echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
	echo "<!-- SVN cat for $url -->\n";
	echo '<cat repo="'.getRepositoryUrl().'" target="'.getTarget().'" rev="'.$rev.'">' . "\n";
	echo '<display src="'.$displayUrl.'" />';
	echo '</cat>';
}
?>