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

/* deprecated
addPlugin('dateformat');
addPlugin('thumbnails');
addPlugin('proplist');
*/

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
		header('Location: '.getWebapp().'open/list/?target='.rawurlencode($target).'&rev='.$rev);
	} else {
		if (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6')) { // IE6 is unable to handle utf-8 in Location header
			// asLink/urlSpecialChars and urlEncodeNames cannot be combined
			header('Location: '.asLink(getRepository()).urlEncodeNames($target));
		} else {
			header('Location: '.asLink($file->getUrl()));
		}
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

/*
> curl -s "http://localhost/repos/open/?target=/test/trunk/fa/a.txt" -I
127.0.0.1 - - [26/Sep/2007:09:06:37 +0200] "HEAD /data/test/trunk/fa/a.txt?serv=json HTTP/1.1" 401 -
127.0.0.1 - - [26/Sep/2007:09:06:38 +0200] "HEAD /data/test/trunk/fa/a.txt?serv=json HTTP/1.1" 401 -
127.0.0.1 - - [26/Sep/2007:09:06:37 +0200] "HEAD /repos/open/?target=/test/trunk/fa/a.txt HTTP/1.1" 401 -

> curl -s "http://localhost/repos/open/?target=/test/trunk/fa/a.txt"
127.0.0.1 - - [26/Sep/2007:09:08:46 +0200] "HEAD /data/test/trunk/fa/a.txt?serv=json HTTP/1.1" 401 -
127.0.0.1 - - [26/Sep/2007:09:08:46 +0200] "HEAD /data/test/trunk/fa/a.txt?serv=json HTTP/1.1" 401 -
127.0.0.1 - - [26/Sep/2007:09:08:47 +0200] "PROPFIND /data/test/trunk/fa/a.txt HTTP/1.1" 401 836
127.0.0.1 - "" [26/Sep/2007:09:08:47 +0200] "PROPFIND /data/test/trunk/fa/a.txt HTTP/1.1" 401 836
127.0.0.1 - "" [26/Sep/2007:09:08:47 +0200] "PROPFIND /data/test/trunk/fa/a.txt HTTP/1.1" 401 836
127.0.0.1 - - [26/Sep/2007:09:08:46 +0200] "GET /repos/open/?target=/test/trunk/fa/a.txt HTTP/1.1" 401 1967
*/

?>
