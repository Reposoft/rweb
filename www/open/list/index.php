<?php
/**
 * Returns 'svn list' for webservice calls,
 * for example to the details plugin
 */

// get the details of a file or folder and return as XML
require( dirname(dirname(__FILE__))."/SvnOpen.class.php" );

// format could be specified as paramter
Validation::expect('target');
$url = getTargetUrl();

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();

$list = new SvnOpen('list');
$list->addArgOption('--xml');
$list->addArgOption('--incremental');
if ($rev) {
	$list->addArgUrlPeg($url, $rev);
} else {
	$list->addArgUrl($url);
}

if ($list->exec()) {
	trigger_error('Could not read entry for URL '.$url, E_USER_ERROR);
}

// some custom parameters needed in the XML. Url needed for navigation http/https.
$head = '<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="'.asLink(getWebapp()).'view/list.xsl"?>
<lists repo="'.asLink(getRepository()).'" target="'.xmlEncodePath(getTarget()) // @path in list is full url so it can't be used
	.'" name="'.xmlEncodePath(getPathName(getTarget())).'"'.($rev ? ' rev="'.$rev.'"' : '').'>
';
// Note that SVN returns the non-ssl url, which might break IE transformation. Could have a path here (targetUrl) too.
$foot = '</lists>';
$extra = strlen($head) + strlen($foot);

$out = $list->getOutput();
$linebreak = "\n";
$length = $list->getContentLength() + strlen($head) + strlen($foot);
if ($linebreak) $length += count($out)*strlen($linebreak);

header('Content-Type: text/xml; charset=utf-8');
header('Content-Length: '.$length);

echo($head);
for ($i=0; $i<count($out); $i++) {
	echo($out[$i].$linebreak);
}
echo($foot);

?>