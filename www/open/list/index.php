<?php
/**
 * Returns 'svn list' for webservice calls,
 * for example to the details plugin
 */

// get the details of a file or folder and return as XML
require( dirname(dirname(__FILE__))."/SvnOpen.class.php" );

// format could be specified as paramter

$url = getTargetUrl();
if (!$url) trigger_error("'target' must be set");

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();

$list = new SvnOpen('list');
$list->addArgOption('--xml');
$list->addArgOption('--incremental');
if ($rev) $list->addArgOption('-r', $rev, false);
$list->addArgUrl($url);

$list->exec();

$head = '<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="/repos/view/list.xsl"?>
<lists>
';
$foot = '</lists>';
$extra = strlen($head) + strlen($foot);

$out = $list->getOutput();
if ($rev) {
	$attr = ' revision="'.$rev.'" ';
	$out[0] = $out[0].$attr; // line was "<list"
	$extra += strlen($attr);
}

header('Content-Type: text/xml; charset=utf-8');
header('Content-Length: '.($list->getContentLength() + $extra));

echo($head);
for ($i=0; $i<count($out); $i++) {
	echo($out[$i]);
}
echo($foot);

?>