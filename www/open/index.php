<?php
/**
 * Displays a page that describes the different viewing options.
 * It would be possible to have different template HTML
 * for different MIME types, but currently it is the same template for all.
 * @package open
 */
require(dirname(dirname(__FILE__)).'/conf/Presentation.class.php');
require(dirname(__FILE__)."/SvnOpenFile.class.php" );

// get file to open
$url = getTargetUrl();
if (strEnds($url, '/')) {
	header('Location: '.$url);
	exit;
}

$type = substr($url, strrpos($url, '.') + 1);

// iCalendar files
if ($type=='ics') {
		setcookie("repos-calendar", $url, time()+3600, '/');
	header("Location: ".getWebapp()."tools/calendar/");
	exit;
}

// new handling

// TODO identify folders, even without trailing slash, for example when coming from history

$revisionRule = new RevisionRule();

$rev = $revisionRule->getValue();

$file = new SvnOpenFile(getTarget(), $rev);

$p = new Presentation();
$p->assign_by_ref('file', $file);
// for links to other operations we use the original parameters
$p->assign('rev', $rev);
$p->assign('target', getTarget());
// all set
$p->display();

// go directly to the resource in repository // header("Location: ".getConfig('repo_root')."$url");

?>