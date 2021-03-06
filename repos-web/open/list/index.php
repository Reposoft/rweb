<?php
/**
 * Returns 'svn list' for webservice calls,
 * for example to the details plugin
 */

// get the details of a file or folder and return as XML
require( dirname(dirname(__FILE__))."/SvnOpen.class.php" );

// format could be specified as paramter
Validation::expect('target');
$target = getTarget();
$url = getTargetUrl();

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();

$list = new SvnOpen('list');
$list->addArgOption('--xml');
$list->addArgOption('--incremental');

// TODO subversion 1.5 comes with a "depth" argument instead
// maybe that one is better at handling authirization denied in recursive list
$recursive = isset($_GET['recursive']) && $_GET['recursive'];
if ($recursive) $list->addArgOption('-R');

if ($rev) {
	$list->addArgUrlPeg($url, $rev);
} else {
	$list->addArgUrl($url);
}

if ($list->exec()) {
	/* recursive: very sucky - http://svn.haxx.se/users/archive-2007-04/0322.shtml
	[0] => svn: PROPFIND request failed on '/data/demoproject/trunk/noaccess'
    [1] => svn: PROPFIND of '/data/demoproject/trunk/noaccess': 403 Forbidden (http://localhost)
	 */
	// should probably have some generic logic for command fallback,
	// * list -R / list --depth
	// * sparse checkout at new verwion of file / old concept for svn 1.4
	$list = new SvnOpen('list', true);
	$list->addArgOption('--incremental');	
	if ($rev) {
		$list->addArgUrlPeg($url, $rev);
	} else {
		$list->addArgUrl($url);
	}
	if ($list->exec()) {
		trigger_error('Could not read entry for URL '.$url, E_USER_ERROR);
	}
	// TODO flag recursive or not in result, add button to switch (greyed out if not allowed?)
}

// to simplify link logic we must assume that this is a folder, but when coming from log we get no trailing slash
if (!strEnds($target, '/')) $target = $target.'/';

// some custom parameters needed in the XML. Url needed for navigation http/https.
$head = '<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="'.asLink(getWebapp()).'view/list.xsl"?>
<lists repo="'.asLink(getRepository()).'"'
	.' target="'.xmlEncodePath($target).'"' // @path in list is full url so it can't be used
	.' name="'.xmlEncodePath(getPathName(getTarget())).'"'
	.(strlen($target)>2 ? ' parent="'.getParent($target).'"' : '') // easier than to get parent using xslt functions
	.($rev ? ' rev="'.$rev.'"' : '')
	.(isset($_REQUEST['base']) ? ' base="'.$_REQUEST['base'].'"' : '')
	.($recursive ? ' recursive="yes"' : '')
	.'>
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
