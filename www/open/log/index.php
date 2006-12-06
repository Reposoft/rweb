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
// TODO add validation for revision numbers, some logic for that found in login.inc.php
$torev = getParameter('torev');
$fromrev = getParameter('fromrev', '{20000101T0000}');

// log a datetime interval, any dates are accepted as long as from < to
// REMOVE and use torev and fromrev instead with conventions from 
// http://svnbook.red-bean.com/nightly/en/svn-book.html#svn.tour.revs.numbers
$todate = getParameter('todate');
$fromdate = getParameter('fromdate');

// log only one revision (a changeset)
$rev = getParameter('rev'); 

// set limit +1 to be able to see if there are more entries
$cmd = 'log -v --xml --incremental --limit '.($limit+1);
if ($rev) {
	$cmd .= ' -r '.$rev;
} else if ($torev) {
	// reverse order, always return revisions in descending order
	$cmd .= ' -r '.$torev.':'.$fromrev;
}
$cmd .= ' '.escapeArgument($url);

// read the log to memory, size is limited and the browser needs the complete xml before rendering anyway
$log = login_svnRun($cmd);
if (($ret=array_pop($log))!=0) { login_handleSvnError($cmd, $ret); exit; } // need a real command class that does exist

// count entries
$size = 0;
$entries = 0;
$limited = false;
$lastrev = 0;
for ($i=0; $i<count($log); $i++) {
	$line = $log[$i];
	if (strBegins($line, '<logentry')) {
		$entries++;
		if ($entries > $limit) $limited = true;
	}
	if (preg_match('/^\s*revision="(\d+)"/', $line, $matches)) {
		$lastrev = $matches[1];
	}
	if ($limited) {
		$log[$i] = ''; // unset not needed (would destory loop), empty lines make no difference after logentry
		$line = '';
	}
	$size += strlen($line)+1; // newline will be added
}

// build xml
$head = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
$head .= '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
$head .= '<log repo="'.getRepository().'" path="'.getTarget().'" web="'.getWebapp().'" static="'.getWebappStatic().'"';
if ($singlefile) $head .= ' file="'.basename(getTarget()).'"';
if ($limited) $head .= ' limit="'.$limit.'" limitrev="'.$lastrev.'"';
$head .= ">\n";

$foot = "\n</log>\n"; 

function setContentLength($bytes) {
	header('Content-Length: '.$bytes);
}
function setContentType($mimetype, $charser=null) {
	header('Content-Type: '.$mimetype);
}

setContentType('text/xml');
// not needed? setContentLength($size);

echo $head;
echo implode("\n", $log);
echo $foot;
?>