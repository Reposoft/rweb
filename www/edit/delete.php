<?php
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));
require( PARENT_DIR."/conf/language.inc.php" );
require( PARENT_DIR."/smarty/smarty.inc.php" );
// automatically check access right to the current folder
require( PARENT_DIR."/account/login.inc.php" );

if (isset($_GET['message'])) {
	delete($_GET['message']); 
} else {
	$target = rtrim(getTargetUrl(),'/'); //folder should also be without tailing slash
	$smarty = getTemplateEngine();
	$smarty->assign('target', $target);
	$smarty->assign('targetname', basename($target));
	$smarty->assign('parent', substr($target, 0, strrpos($target,'/')));
	$smarty->assign('repo', getRepositoryUrl());
	$smarty->display(DIR.getLocaleFile());
}

function delete($message) {
	$target = escapeshellcmd(getTargetUrl());
	$cmd = 'delete -m "'.escapeshellcmd($message)."\" $target";
	$result = exec(getSvnCommand() . $cmd);
	if (strlen($result) < 1) {
		echo ("Error. Could not delete item using: svn " . $cmd);
		exit;
	}
	ereg("", $result, $rev);
	$smarty = getTemplateEngine();
	$smarty->assign('result', $result);
	$smarty->assign('target', $target);
	$smarty->assign('revision', $rev[0]);
	$smarty->assign('parentUri', dirname(rtrim($target),'/'));
	$smarty->display(DIR.getLocaleFile('delete_done'));
}
?>