<?php
require_once( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require_once( dirname(dirname(dirname(__FILE__))) . "/account/login.inc.php" );

$url = getTargetUrl();
if(!isFile($url)) {
	trigger_error("Error: File not specified. Directories can not be shown here.");
	exit;
}
$rev = getRevision();
if(!$rev) {
	trigger_error("Error: Version parameter (\"rev\") not specified.");
	exit;
}

$filename = basename($url);
$target = getTarget();
$downloadUrl = repos_getSelfUrl().'?'.repos_getSelfQuery().'&open';

$mimetype = login_getMimeType($url.'@'.$rev);

// download
if (isset($_GET['open'])) {
	if ($mimetype) {
		header('Content-type: '.$mimetype);
	} else {
		header('Content-type: application/x-unknown');
	}
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	$returnvalue = login_svnPassthruFile($url, $rev);
	if ($returnvalue) {
		trigger_error("Error. Could not read '$url' version $rev.");
	}
// show
} else {
	$p = new Presentation();
	$p->assign('target', $target);
	$p->assign('revision', $rev);
	$p->assign('dowloandUrl', $downloadUrl);
	$p->assign('targetpeg', $url.'@'.$rev);
	// exit points
	$referer = getHttpReferer();
	if (!empty($referer) && strContains($referer, '/open/log/')) {
		$p->assign('logurl', $referer);
		$p->assign('repository', getRepository().strAfter($referer, 'target='));
	} else {
		$existingFolder = login_getFirstNon404Parent(getParent($url), &$s);
		$p->assign('repository', $existingFolder);
		$p->assign('logurl', '../log/target='.strAfter($existingFolder, getRepository()));
	}
	$p->display();
}

?>