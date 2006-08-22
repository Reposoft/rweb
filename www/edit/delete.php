<?php
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));
require( PARENT_DIR."/conf/language.inc.php" );
require( PARENT_DIR."/smarty/smarty.inc.php" );
// automatically check access right to the current folder
require( PARENT_DIR."/conf/repos.properties.php" ); // can not use referrer to get repo here because 'path' is not in the referrer
require( PARENT_DIR."/account/login.inc.php" );

if (isset($_GET['message'])) {
	delete($_GET['message']); 
} else {
	$target = rtrim(getTarget(),'/'); //folder should also be without tailing slash
	$smarty = getTemplateEngine();
	$smarty->assign('target', $target);
	$smarty->assign('targetname', basename($target));
	$smarty->assign('parent', dirname($target));
	$smarty->assign('repo', getRepositoryUrl());
	$smarty->display(DIR.getLocaleFile());
}

function delete($message) {
	$target = escapeshellcmd(getTargetUrl());
	$cmd = 'delete -m "'.escapeshellcmd($message)."\" $target";
	$result = exec(getSvnCommand() . $cmd);
	if (strlen($result) < 1) {
		echo ("Error. Could not delete item using:\nsvn " . $cmd);
		exit;
	}
	ereg("^Committed revision ([0-9]+)\.", $result, $rev);
	$smarty = getTemplateEngine();
	$smarty->assign('result', $result);
	$smarty->assign('target', $target);
	$smarty->assign('revision', $rev[0]);
	$smarty->assign('parentUri', dirname(rtrim($target,'/')));
	$smarty->display(DIR.getLocaleFile('delete_done'));
}
?>