<?php
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));
require( PARENT_DIR."/conf/language.inc.php" );
require( PARENT_DIR."/smarty/smarty.inc.php" );
// automatically check access right to the current folder
require( PARENT_DIR."/account/login.inc.php" );

if (isset($_GET['name'])) {
	createFolder(getTargetUrl(),$_GET['name'],$_GET['message']); 
} else {
	$target = getTarget();
	$smarty = getTemplateEngine();
	$smarty->assign('target', urlEncodeNames($target));
	$smarty->assign('targetname', $target);
	$smarty->assign('repo', getRepositoryUrl());
	$smarty->display(DIR.getLocaleFile());
}

function createFolder($parentUri, $name, $message) {
	$newfolder = escapeshellcmd(rtrim($parentUri,'/').'/'.urlEncodeNames($name));
	$dir = tmpdir(null, 'repos_');
	$cmd = 'import -m "'.escapeshellcmd($message)."\" $dir $newfolder";
	$result = exec(getSvnCommand() . $cmd);
	rmdir($dir);
	if (strlen($result) < 1) {
		echo ("Error. Could not create folder using:\nsvn " . $cmd);
		exit;
	}
	$smarty = getTemplateEngine();
	$smarty->assign('result', $result);
	$smarty->assign('newfolder', $newfolder);
	$smarty->assign('newfoldername', basename($newfolder));
	$smarty->assign('target', $parentUri);
	$smarty->display(DIR.getLocaleFile('mkdir_done'));
}

// Creates a directory with a unique name
// at the specified with the specified prefix.
// Returns directory name on success, false otherwise
function tmpdir($path, $prefix)
{
       // Use PHP's tmpfile function to create a temporary
       // directory name. Delete the file and keep the name.
       $tempname = tempnam($path,$prefix);
       if (!$tempname)
               return false;

       if (!unlink($tempname))
               return false;

       // Create the temporary directory and returns its name.
       if (mkdir($tempname))
               return $tempname;

       return false;
}
?>