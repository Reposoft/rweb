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
// for deciding if View in Repos link should be displayed
require(dirname(__FILE__).'/file/images.inc.php');

// get file to open
$target = getTarget();

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();

// when coming from history 'fromrev' is before the selected commit
$fromrevRule = new RevisionRule('fromrev');
$fromrev = $fromrevRule->getValue();

$file = new SvnOpenFile($target, $rev);

// support redirect directly to real resorce (for services that don't know repository root but have target and base)
if (isset($_GET['redirect']) && $_GET['redirect']) {
	if ($rev) {
		// for old revisions SvnOpenFile detects folder even if trailing slash is missing
		if (!strEnds($target, '/')) $target .= '/';
		// does not use getRepository so "base" must be added manually
		$b = isset($_REQUEST['base']) ? '&base='.$_REQUEST['base'] : '';
		header('Location: '.getWebapp().'open/list/?target='.rawurlencode($target).$b.'&rev='.$rev);
	} else {
		if (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6')) { // IE6 is unable to handle utf-8 in Location header
			// asLink/urlSpecialChars and urlEncodeNames cannot be combined
			header('Location: '.asLink(getRepository()).urlEncodeNames($target));
		} else {
			header('Location: '.asLink($file->getUrlNoquery())); // TODO should we support rev in redirect?
		}
	}
	exit;
}

// show details page
$p = Presentation::getInstance();
$p->assign_by_ref('file', $file);
// for links to other operations we use the original parameters
$p->assign('target', $target);
if ($fromrev) $p->assign('fromrev', $fromrev);
// display a short log for the file on the edit page
if (!isset($_REQUEST['history']) || $_REQUEST['history'] != 'false') {
	require(dirname(__FILE__).'/getlog.php');
	$p->assign('log', getLog(getTargetUrl(), 10, $rev));
} else {
	$p->assign('log', array());
}
require(dirname(__FILE__).'/getproplist.php');
$p->assign('proplist', getProplistGrouped(getTargetUrl(), $rev));
// before entering smarty template code, read some file info so that in the event of access error
// a formatted error page is presented (errors in template can ony be presented in plaintext)
// When we had a dedicated "edit" page the same was done with $file->isWritable();$file->isLocked();
$file->getRevision(); // maybe file->isFolder above will be sufficient if SvnOpenFile is simplified.
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
