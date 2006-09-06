<?php
require_once( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require_once( dirname(dirname(dirname(__FILE__))) . "/account/login.inc.php" );

$url = getTargetUrl();
if(!isTargetFile()) {
	trigger_error("Error: File not specified. Directories can not be shown here.");
	exit;
}
$rev = getRevision();
if(!$rev) {
	trigger_error("Error: Version parameter (\"rev\") not specified.");
	exit;
}

$nextUrl = getReferer();
if (!$nextUrl) {
	$nextUrl = dirname($url);
}

$filename = getFile();
$target = getTarget();
$downloadUrl = repos_getSelfUrl().'?'.repos_getSelfQuery().'&open';

$mimetype = getMimetype($url, $rev);

// download
if (isset($_GET['open'])) {
	if ($mimetype) {
		header('Content-type: '.$mimetype);
	} else {
		header('Content-type: text/plain; charset=utf-8');
	}
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	$returnvalue = doPassthru($url, $rev);
	if ($returnvalue) {
		trigger_error("Error. Could not read '$url' version $rev.");
	}
// show
} else {
	$p = new Presentation();
	$p->assign('target', $target);
	$p->assign('revision', $rev);
	$p->assign('next', $nextUrl);
	$p->assign('dowloandUrl', $downloadUrl);
	$p->display();
	doPassthru($url, $rev);
	
	?>
	</pre></div>
	<div class="footer"></div>
	</div>
	</body>
	</html>
	<?php
}

function doPassthru($targetUrl, $revision) {
	//$cmd = 'cat' . ' -r'.$revision . ' "'.$targetUrl.'"';
	$cmd = 'cat '.escapeArgument($targetUrl.'@'.$revision); // using "peg" revision
	$returnvalue = login_svnPassthru($cmd);
	return $returnvalue;
}

function getMimeType($targetUrl, $revision) {
	$cmd = 'propget -r'.$revision.' svn:mime-type '.escapeArgument($targetUrl);
	$result = login_svnRun($cmd);
	$returnvalue = array_pop($result);
	if ($returnvalue) {
		trigger_error("Could not find the file '$targetUrl' in repository version $revision.");
	}
	if (count($result) == 0) {
		return false; // svn:mime-type not set
	}
	return $result[0];
}
?>