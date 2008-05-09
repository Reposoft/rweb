<?php
/**
 * Batch operations on the ACL in the repository administration folder.
 * 
 * ?create=[username]
 * ?delete=[username]
 * 
 * Combined with admin/create/ and admin/password/ this should be all
 * that is needed to create a user account.
 * 
 * @package account
 */

define('ADMIN_FOLDER', '/administration/');
define('ACCESS_FILE', 'repos.accs');

require('../../admin.inc.php');
require(ReposWeb.'open/SvnOpenFile.class.php');
require(ReposWeb.'edit/SvnEdit.class.php');
require(ReposWeb.'conf/Presentation.class.php');

$file = new SvnOpenFile(ADMIN_FOLDER.ACCESS_FILE);

if ($file->getStatus()!=200) trigger_error('This account does not have access to the ACL', E_USER_ERROR);

$wc = System::getTempFolder('acl');

$checkout = new SvnEdit('checkout');
$checkout->addArgOption('--non-recursive');
$checkout->addArgUrl(getParent($file->getUrl()));
$checkout->addArgPath($wc);
if ($checkout->exec('Checked out current ACL')) {
	trigger_error('User not created. Could not check out current ACL.', E_USER_ERROR);
}

$acl = $wc.ACCESS_FILE;

// select operation
if (isset($_REQUEST['create'])) {
	$username = $_REQUEST['create'];
	aclCreateUser($username, $acl);
	aclCommit($wc, "Created access for new account '$username' to personal folder.");
} else if (isset($_REQUEST['delete'])) {
	$username = $_REQUEST['delete'];
	aclDeleteUser($username, $acl);
	aclCommit($wc, "Deleted access control for folder '/$username'.");
} else {
	System::deleteFolder($wc);
	trigger_error('No operation selected', E_USER_ERROR);
}

System::deleteFolder($wc);

$next = getTargetUrl(ADMIN_FOLDER);
$p = Presentation::getInstance();
$p->assign('target', $file->getPath()); // assigned by the other edit pages for commandbar
displayEdit($p, $next);
// page done. below are helper functions.

// shared commit changes logic
function aclCommit($wc, $commitMessage) {
	$update = new SvnEdit('update');
	$update->addArgPath($wc);
	$update->exec('Checked for conflicts');
	$commit = new SvnEdit('commit');
	$commit->addArgPath($wc);
	$commit->setMessage($commitMessage);
	$commit->exec('Updated ACL committed');
}

/**
 * Adds a user to the ACL (with no group memberships)
 *
 * @param String $username the username to create
 * @param String $aclFile the path to the subversion ACL file
 */
function aclCreateUser($username, $aclFile) {
	if (!file_exists($aclFile)) trigger_error("Can not access checked out file $aclFile");
	if (aclUserExists($username, $aclFile)) trigger_error("User $username already exists", E_USER_ERROR);
	$nl = System::getNewline();
	$f = fopen($aclFile, 'a');
	fwrite($f, "$nl");
	fwrite($f, "[/$username]$nl");
	fwrite($f, "$username = rw$nl");
	fwrite($f, "* = $nl"); // no access from root, even for administrators
	fclose($f);
}

/**
 * Adds a user to the ACL (with no group memberships)
 *
 * @param String $username the username to create
 * @param String $aclFile the path to the subversion ACL file
 */
function aclDeleteUser($username, $aclFile) {
	if (!file_exists($aclFile)) trigger_error("Can not access checked out file $aclFile");
	if (aclUserExists($username, $aclFile)) trigger_error("User $username already exists", E_USER_ERROR);
	_aclDeletePath("/$username", $aclFile);
}

function _aclDeletePath($path, $aclFile) {
	$tmp = fopen($aclFile.".tmp", 'w');
	$f = fopen($aclFile, 'r');
	$cut = false;
	while (!feof($f)) {
        $buffer = fgets($f);
        if ($cut && preg_match('/^\[.*\]/', $buffer)) {
        		$cut = false;
        }
        if (preg_match('/^\['.preg_quote($path,'/').'\]\s*/', $buffer)) {
        		$cut = true;
        }
        if (!$cut) fwrite($tmp, $buffer);
   }
	fclose($f);
	fclose($tmp);
	if (!copy($aclFile.".tmp", $aclFile)) {
		trigger_error("Failed to write new ACL file to $aclFile");
	}
	System::deleteFile($aclFile.".tmp");
}

/**
 * Checks if user already exists in ACL
 *
 * @param String $username the username to create
 * @param String $aclFile the path to the subversion ACL file
 */
function aclUserExists($username, $aclFile) {
	return false;
}

?>