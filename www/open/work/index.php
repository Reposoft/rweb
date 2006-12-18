<?php
require_once( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require_once( dirname(dirname(dirname(__FILE__))) . "/account/login.inc.php" );

$target = getTarget();
$downloadUrl = '../../open/cat/?target='.rawurlencode($target).'&rev=HEAD&open';
$repository = getParent(getTargetUrl());

// it is possible to bring up the save as dialog or a client download here, before the new page is shown

$p = new Presentation();

$p->assign('downloadUrl', $downloadUrl);
$p->assign('repository', $repository);

// $requestUrlArray = array('target' => $target, 'oldname' => 'test.xml', 'tofolder' => 'branches', 'newname' => 'test2.xml', 'move' => '0', 'message' => '', 'submit' => 'Copy');
$oldname = basename($target);

if (strpos($target, '/trunk/') === FALSE){
	$tofolder = false;
} else {
	$tofolder = substr_replace(dirname($target), '/branches/', strpos(dirname($target), '/trunk/'));
}
$newname = time() . '-' . getReposUser() . '-' . $oldname;

$p->assign('pathToCopyForm', '../../edit/copy/');
$p->assign('target', $target);
$p->assign('oldname', $oldname);
$p->assign('tofolder', $tofolder);
$p->assign('newname', $newname);
$p->assign('message', '');
$p->display();

$svnlog='C:\>svn log -v --xml --stop-on-copy http://localhost/testrepo/demoproject/branch
es/1166113178-test-test.xml
<?xml version="1.0"?>
<log>
<logentry
   revision="16">
<author>test</author>
<date>2006-12-14T16:28:05.404875Z</date>
<paths>
<path
   action="M">/demoproject/branches/1166113178-test-test.xml</path>
</paths>
<msg></msg>
</logentry>
<logentry
   revision="14">
<author>test</author>
<date>2006-12-14T16:20:03.514250Z</date>
<paths>
<path
   copyfrom-path="/demoproject/trunk/public/test.xml"
   copyfrom-rev="13"
   action="A">/demoproject/branches/1166113178-test-test.xml</path>
</paths>
<msg>/demoproject/trunk/public/test.xml</msg>
</logentry>
</log>';
?>
