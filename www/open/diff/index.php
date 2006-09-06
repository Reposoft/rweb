<?php
// TODO convert to the same concept as 'cat'
require_once(dirname(dirname(dirname(__FILE__)))."/account/login.inc.php" );

define('STYLESHEET','../svnlayout/repos.xsl');

$url = getTargetUrl();
$revfrom = $_GET['revfrom'];
$revto = $_GET['revto'];
if(empty($revfrom) || empty($revto)) {
	echo "Argument error: 'revfrom' and 'revto' not specified.";
	exit;
}
$revisions = ' -r '.$revfrom.':'.$revto;

$cmd = 'diff' . $revisions . ' "'.$url.'"';

// passthrough with stylesheet
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
echo "<!-- SVN diff for .$url. -->\n";
echo '<diff repo="'.getRepositoryUrl().'" target="'.$_GET['target'].'" revfrom="'.$revfrom.'" revto="'.$revto.'"><plaintext>' . "\n";
echo "<![CDATA[\n";
$returnvalue = login_svnPassthru($cmd);
if ($returnvalue) login_handleSvnError($cmd, $returnvalue);
echo "]]>\n";
echo '</plaintext></diff>';
?>