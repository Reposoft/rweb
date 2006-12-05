<?php

require_once(dirname(dirname(dirname(__FILE__))) . "/account/login.inc.php" );

define('STYLESHEET','../../view/log.xsl');

// the resource to log
$url = getTargetUrl();
$singlefile = false;
if (isFile($url)) $singlefile = true;

// read request parameters
function getParameter($name, $default=null) {
	if (isset($_REQUEST[$name])) return $_REQUEST[$name];
	return $default;
}

// always use a limit for the number of records, to prevent big transforms
// if the limit is active (meaning that there are more records than returned) the xml sets the limit attribute
$limit = getParameter('limit', '100');

// log a revision interval, using integer numbers to flip between log pages
// revisions must exist for the given target
$torev = getParameter('torev');
$fromrev = getParameter('fromrev', '{20000101T0000}');

// log a datetime interval, any dates are accepted as long as from < to
// REMOVE and use torev and fromrev instead with conventions from 
// http://svnbook.red-bean.com/nightly/en/svn-book.html#svn.tour.revs.numbers
$todate = getParameter('todate');
$fromdate = getParameter('fromdate');

// log only one revision (a changeset)
$rev = getParameter('rev'); 

$cmd = 'log -v --xml --incremental --limit '.$limit;
if ($torev) {
	// reverse order, always return revisions in descending order
	$cmd .= ' -r '.$torev.':'.$fromrev;
}
$cmd .= ' '.escapeArgument($url);

// start output

// Set HTTP output character encoding to SJIS
//should be configured in server //mb_http_output('UTF-8');

// passthrough with stylesheet
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
echo "<!-- SVN log for $url -->\n";
echo '<log repo="'.getRepository().'" path="'.getTarget().'" web="'.getWebapp().'" static="'.getWebappStatic().'">' . "\n";
$returnvalue = login_svnPassthru($cmd);
if ($returnvalue) login_handleSvnError($cmd, $returnvalue);
echo '</log>';
?>