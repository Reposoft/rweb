<?php
// TODO: Cat is no good as XML data. Could always contain illegal chars. Just make it plain html instead.
require_once( dirname(dirname(__FILE__)) . "/account/login.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');

$url = getTargetUrl();
if(!isTargetFile()) {
	trigger_error("Error: File not specified. Directories can not be shown here.");
	exit;
}
if(!isset($_GET['rev'])) {
	trigger_error("Error: Version parameter (\"rev\") not specified.");
	exit;
}
$rev = $_GET['rev'];
if(!is_numeric($rev)) {
	trigger_error("Error: Version number is '$rev', which is not a number.");
}
$filename = getFile();

// passthrough with stylesheet
if (isset($_GET['open'])) {
	$mimetype = getMimetype($url, $rev);
	if ($mimetype) {
		header('Content-type: '.$mimetype);
	} else {
		header('Content-type: text/plain; charset=utf-8');
	}
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	$returnvalue = doPassthru($url, $rev);
	if ($returnvalue) {
		trigger_error("Error. Could not read '$url' version $rev.");
	}
} else if (isset($_GET['display'])) {
	header('Content-type: text/plain; charset=utf-8');
	$returnvalue = doPassthru($url, $rev);
	if ($returnvalue) {
		trigger_error("Error. Could not read '$url' version $rev.");
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

function doPassthru($targetUrl, $revision) {
	//$cmd = 'cat' . ' -r'.$revision . ' "'.$targetUrl.'"';
	$cmd = 'cat "'.$targetUrl.'@'.$revision.'"'; // using "peg" revision
	$returnvalue = login_svnPassthru($cmd);
	return $returnvalue;
}

function getMimeType($targetUrl, $revision) {
	$cmd = 'propget -r'.$revision.' svn:mime-type "'.$targetUrl.'"';
	$result = login_svnRun($cmd);
	$returnvalue = array_pop($result);
	if ($returnvalue) {
		trigger_error("Could not find the file '$targetUrl' in repository version $revision.");
	}
	if (count($result) == 0) {
		return false; // svn:mime-type not set
	}
	return $result[0];
}
?>