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
if ($rev) $list->addArgOption('-r', $rev, false);
$list->addArgUrl($url);

$list->exec();

// some custom parameters needed in the XML. Url needed for navigation http/https.
$head = '<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="/repos/view/list.xsl"?>
<lists repo="'.getRepositoryUrl().'" target="'.getTarget().'"'.($rev ? ' rev="'.$rev.'"' : '').'>
';
// Note that SVN returns the non-ssl url, which might break IE transformation. Could have a path here (targetUrl) too.
$foot = '</lists>';
$extra = strlen($head) + strlen($foot);

header('Content-Type: text/xml; charset=utf-8');
header('Content-Length: '.($list->getContentLength() + strlen($head) + strlen($foot)));

$out = $list->getOutput();
echo($head);
for ($i=0; $i<count($out); $i++) {
	echo($out[$i]);
}
echo($foot);

?>