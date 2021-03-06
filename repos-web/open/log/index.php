<?php

require_once(dirname(dirname(__FILE__)) . "/SvnOpen.class.php" );

$webapp = asLink(getWebapp()); // asLink to get the same host
define('STYLESHEET',$webapp.'view/log.xsl');

// the resource to log
$url = getTargetUrl();
$singlefile = false;
if (isFile($url)) $singlefile = true;

// read request parameters
function getParameter($name, $default=null) {
	if (isset($_REQUEST[$name])) return $_REQUEST[$name];
	return $default;
}

// TODO move to some generic place and define constants for content types
function setContentLength($bytes) {
	header('Content-Length: '.$bytes);
}
function setContentType($mimetype, $charser=null) {
	header('Content-Type: '.$mimetype);
}

$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : false;

// always use a limit for the number of records, to prevent big transforms
// if the limit is active (meaning that there are more records than returned) the xml sets the limit attribute
$limit = getParameter('limit', '20');

// log a revision interval, using integer numbers to flip between log pages
// revisions must exist for the given target
// TODO add validation for revision numbers, some logic for that found in login.inc.php
$torev = getParameter('torev');
$fromrev = getParameter('fromrev', '0');

// log a datetime interval, any dates are accepted as long as from < to
// REMOVE and use torev and fromrev instead with conventions from 
// http://svnbook.red-bean.com/nightly/en/svn-book.html#svn.tour.revs.numbers
// for example value {20050101T0000}
$todate = getParameter('todate');
$fromdate = getParameter('fromdate');

// log only one revision (a changeset)
$rev = getParameter('rev'); 

$command = new SvnOpen('log', true);
$command->addArgOption('--xml');
// TODO in 1.4 make non-verbose default and add toggle button to command bar
// The logcollapse plugin should expand individual entries from non-verbose using single entry log xml (rev param)
// Also for non-verbose log on a file the details button must be shown with the headline
if (!isset($_REQUEST['verbose']) || $_REQUEST['verbose']) {
	$command->addArgOption('-v');
}
if ($accept !== 'application/json') {
	$command->addArgOption('--incremental');
}
// set limit +1 to be able to see if there are more entries
$command->addArgOption('--limit', $limit+1, false); // limit is a number, if not this will be an empty string (so it's safe)
if ($rev) {
	$command->addArgOption('-c', $rev);
	$command->addArgUrlPeg($url, $rev);
} else if ($torev) {
	// reverse order, always return revisions in descending order
	$command->addArgRevisionRange($torev.':'.$fromrev);
	$command->addArgUrlPeg($url, $torev);
} else {
	$command->addArgUrl($url);
}

// read the log to memory, size is limited and the browser needs the complete xml before rendering anyway
if ($command->exec()) {
	$message = implode(" \n",$command->getOutput());
	if (preg_match('/.*!svn\/bc\/(\d+)(.*)\'.*path not found/', $message, $matches)) {
		$message = "Path '$matches[2]' not found in revision $matches[1]."; 
	}
	require_once(dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
	//overridden by Presentation://header("HTTP/1.0 404 Not Found");
	trigger_error('Could not read log for URL '.$url.".\n".$message, E_USER_ERROR);
}
$log = $command->getOutput();

if ($accept == 'application/json') {
	$xml = simplexml_load_string(implode("", $log));
	setContentType('application/json');
	echo json_encode($xml);
	echo "\n";
	exit;
}

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
		$log[$i] = ''; // replace extra entry's xml with empty line (unset would destory loop)
		$line = '';
	}
	$size += strlen($line)+1; // newline will be added
}

// build xml
$head = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
$head .= '<?xml-stylesheet type="text/xsl" href="' . STYLESHEET . '"?>' . "\n";
$head .= '<log repo="'.asLink(getRepository()).'" target="'.xmlEncodePath(getTarget())
	.'" name="'.xmlEncodePath(getPathName(getTarget())).'" web="'.asLink(getWebapp()).'"';
if (isset($_REQUEST['base'])) $head .= ' base="'.$_REQUEST['base'].'"';
if ($singlefile) $head .= ' file="'.xmlEncodePath(getPathName(getTarget())).'"';
if ($limited) $head .= ' limit="'.$limit.'" limitrev="'.$lastrev.'"';
$head .= ">\n";

$foot = "\n</log>\n";

setContentType('text/xml');
// not needed? setContentLength($size);

echo $head;
echo implode("\n", $log);
echo $foot;

?>
