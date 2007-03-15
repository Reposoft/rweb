<?php
/**
 * Target operation menu (c) 2005-2007 Staffan Olsson www.repos.se
 * 
 * Displays a page that describes the different viewing options.
 * It would be possible to have different template HTML
 * for different MIME types, but currently it is the same template for all.
 * @package open
 */
require(dirname(dirname(__FILE__)).'/conf/Presentation.class.php');
require(dirname(__FILE__)."/SvnOpenFile.class.php" );

addPlugin('dateformat');
addPlugin('thumbnails');
addPlugin('proplist');

// get file to open
$target = getTarget();

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();

// when coming from history 'fromrev' is before the selected commit
$fromrevRule = new RevisionRule('fromrev');
$fromrev = $fromrevRule->getValue();

$file = new SvnOpenFile($target, $rev);
// identify folders, even without trailing slash, for example when coming from history
if ($file->isFolder()) {
	if ($rev) {
		// for old revisions SvnOpenFile detects folder even if trailing slash is missing
		if (!strEnds($target, '/')) $target .= '/';
		header('Location: '.getWebapp().'open/list/?target='.urlencode($target).'&rev='.$rev);
	} else {
		header('Location: '.$file->getUrl());
	}
	exit;
}

$p = Presentation::getInstance();
$p->assign_by_ref('file', $file);
// for links to other operations we use the original parameters
$p->assign('rev', $rev);
$p->assign('target', getTarget());
if ($fromrev) $p->assign('fromrev', $fromrev);
// all set
$p->display();

?>
