<?php
/**
 * Displays a page that describes the different viewing options.
 * It would be possible to have different template HTML
 * for different MIME types, but currently it is the same template for all.
 * @package open
 */
require(dirname(dirname(__FILE__)).'/conf/Presentation.class.php');
require(dirname(__FILE__)."/SvnOpenFile.class.php" );

addPlugin('dateformat');
addPlugin('thumbnails');

// get file to open
$target = getTarget();

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();

// when coming from history 'fromrev' is before the selected commit
$fromrevRule = new RevisionRule('fromrev');
$fromrev = $fromrevRule->getValue();

// TODO use a HTTP method to check for folder (this is only syntactically)
// should probably be integrated with SvnOpenFile
$url = asLink(getRepository()).$target;
if (isFolder($url) || !strContains(getPathName($url), '.')) {
	if ($rev) {
		header('Location: '.getWebapp().'open/list/?target='.urlencode($target).'&rev='.$rev);
	} else {
		header('Location: '.$url);
	}
	exit;
}

// TODO identify folders, even without trailing slash, for example when coming from history

$file = new SvnOpenFile($target, $rev);

$p = Presentation::getInstance();
$p->assign_by_ref('file', $file);
// for links to other operations we use the original parameters
$p->assign('rev', $rev);
$p->assign('target', getTarget());
if ($fromrev) $p->assign('fromrev', $fromrev);
// all set
$p->display();

// go directly to the resource in repository // header("Location: ".getConfig('repo_root')."$url");

?>
