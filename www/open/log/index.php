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

// always use a limit for the number of records, to prevent big transforms
// if the limit is active (meaning that there are more records than returned) the xml sets the limit attribute
$limit = getParameter('limit', '20');

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

$command = new SvnOpen('log', true);
$command->addArgOption('-v');
$command->addArgOption('--incremental');
// set limit +1 to be able to see if there are more entries
$command->addArgOption('--limit', $limit+1, false); // limit is a number, if not this will be an empty string (so it's safe)
if ($rev) {
	$command->addArgUrlPeg($url, $rev);
} else if ($torev) {
	// reverse order, always return revisions in descending order
	$command->addArgRevisionRange($torev.':'.$fromrev);
	$command->addArgUrl($url);
} else {
	$command->addArgUrl($url);
}

// read the log to memory, size is limited and the browser needs the complete xml before rendering anyway
if ($command->exec()) { 
	handleAuthenticationError($url, $command->getOutput());
	trigger_error('Could not read log for URL '.$url.': '.implode(" \n",$command->getOutput()), E_USER_ERROR);
}
$log = $command->getOutput();

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
$head .= '<log repo="'.asLink(getRepository()).'" target="'.xmlEncodePath(getTarget())
	.'" name="'.xmlEncodePath(getPathName(getTarget())).'" web="'.asLink(getWebapp()).'"';
if ($singlefile) $head .= ' file="'.xmlEncodePath(getPathName(getTarget())).'"';
if ($limited) $head .= ' limit="'.$limit.'" limitrev="'.$lastrev.'"';
$head .= ">\n";

$foot = "\n</log>\n"; 

// TODO move to some generic place and define constants for content types
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

/**
 * If the output of a subversion command says authorization failed,
 * it might be because we have not requested authentication.
 * In that case we can send the authentication header and redo the request.
 * This should be rare because browsers by default resend credentials,
 * but it happens if for example the user authenticated in the repository, like /data/,
 * and this service is located at a different url, like /repos/.
 * @param array $output result of svn command with english locale
 */
function handleAuthenticationError($targetUrl, $output) {
	if (getReposUser()!==false) return;
	if (preg_match('/authorization\s+failed/',$output[1])) {
		// svn command's authentication error does not reveal realm name
		$realm = getAuthName($targetUrl);
		askForCredentials($realm);
		exit; // need extra exit because the one in askForCredentials might exit the include only (?)
	}
}
?>
