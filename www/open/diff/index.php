<?php
// TODO convert to the same concept as 'cat'
require_once(dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require_once(dirname(dirname(dirname(__FILE__)))."/account/login.inc.php" );

$url = getTargetUrl();
if(empty($url) || !isset($_GET['revfrom']) || !isset($_GET['revto'])) {
	trigger_error("Argument error: target, 'revfrom' and 'revto' must be specified.");
	exit;
}
$revfrom = getRevision($_GET['revfrom']);
$revto = getRevision($_GET['revto']);

$revisions = ' -r '.$revfrom.':'.$revto;

$cmd = 'diff' . $revisions . ' '.escapeArgument($url);


$p = new Presentation();
$p->assign('target', $url);
$p->assign('revfrom', $revfrom);
$p->assign('revto', $revto);
$referer = getReferer();
if (empty($referer)) {
	$p->assign('../log/?taget='.dirname($url));
} else if (strpos($referer, '/open/log/')) {
	$p->assign('logurl', $referer);
} else {
	$p->assign('referer', $referer);
}
$p->display();

$returnvalue = login_svnPassthru($cmd);
if ($returnvalue) login_handleSvnError($cmd, $returnvalue);
?>
</pre>
<hr />
</div>
<div class="footer"></div>
</div>
</body>
</html>